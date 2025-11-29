<?php
session_start();
include "includes/db_connect.php";

$email = isset($_SESSION['otp_email']) ? trim($_SESSION['otp_email']) : '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $otp = trim(mysqli_real_escape_string($conn, $_POST['otp']));

    // Fetch user by email and OTP
    $userrow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND otp_code='$otp'"));

    // PHP NOW
    $php_now = date('Y-m-d H:i:s');
    // MySQL NOW for debug
    $mysql_now = mysqli_fetch_row(mysqli_query($conn, "SELECT NOW()"))[0];

    // Debug info
    echo "<div style='background:#efe;border-left:4px solid #080;padding:6px;margin-bottom:10px;'>";
    echo "<b>Debug Info:</b><br>";
    echo "Session Email: " . htmlentities($email) . "<br>";
    echo "Entered OTP: " . htmlentities($otp) . "<br>";
    echo "DB OTP: " . htmlentities($userrow['otp_code'] ?? '') . "<br>";
    echo "DB Expiry: " . htmlentities($userrow['otp_expiry'] ?? '') . "<br>";
    echo "PHP NOW: $php_now<br>";
    echo "MySQL NOW(): $mysql_now<br>";
    echo "</div>";

    if ($userrow) {
        if (strtotime($userrow['otp_expiry']) > time()) {
            $_SESSION['otp_verified'] = 1;
            $msg = "OTP verified! Enter your new password.";
        } else {
            $msg = "OTP expired. Please request a new one.";
        }
    } else {
        $msg = "Invalid OTP.";
    }
}

if (isset($_POST['new_password']) && isset($_SESSION['otp_verified']) && $_SESSION['otp_verified']) {
    $newpass = mysqli_real_escape_string($conn, $_POST['new_password']);
    $password = md5($newpass); // for better security, use password_hash()
    mysqli_query($conn, "UPDATE users SET password='$password', otp_code=NULL, otp_expiry=NULL WHERE email='$email'");
    $msg = "Password changed! <a href='login.php'>Login here</a>.";
    unset($_SESSION['otp_verified']);
    unset($_SESSION['otp_email']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Verify OTP - GrocXpress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        :root {
            --main-blue: #667eea;
            --main-hover: #5a67d8;
            --light: #fff;
            --danger: #e74c3c;
            --success: #009a6f;
            --dark: #2d3748;
            --input-bg: #eef2ff;
            --input-border: #c3d0f5;
        }
        body {
            background: var(--light);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
        }
        .card {
            background: var(--light);
            max-width: 400px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 8px 32px #667eea35, 0 2px 8px #00000017;
            padding: 2.5rem 2rem;
            box-sizing: border-box;
            text-align: center;
            border: 2px solid var(--input-border);
        }
        h1 {
            color: var(--main-blue);
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 0.03em;
        }
        form {
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--main-blue);
            font-size: 1rem;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            font-size: 1rem;
            border: 1.5px solid var(--input-border);
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--dark);
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: var(--main-blue);
        }
        button {
            width: 100%;
            background: var(--main-blue);
            color: #fff;
            font-weight: 800;
            border: none;
            padding: 13px 0;
            border-radius: 33px;
            font-size: 1.1rem;
            transition: background 0.2s ease-in-out;
            cursor: pointer;
            letter-spacing: 0.03em;
            box-shadow: 0 2px 18px #667eea80;
        }
        button:hover {
            background: var(--main-hover);
        }
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 7px;
            font-weight: 700;
            font-size: 1rem;
            text-align: center;
        }
        .message.error {
            background: #ffe6e6;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        .message.success {
            background: #e9fbe9;
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        a.back-link {
            display: inline-block;
            margin-top: 18px;
            color: var(--main-blue);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s;
        }
        a.back-link:hover {
            color: var(--main-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Verify OTP</h1>

        <?php if ($msg): ?>
            <div class="message <?= strpos($msg, 'invalid') !== false || strpos($msg, 'expired') !== false ? 'error' : 'success' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['otp_verified'])): ?>
            <form method="post" autocomplete="off" novalidate>
                <label for="otp">OTP Code</label>
                <input type="text" id="otp" name="otp" maxlength="6" required pattern="\d{6}" title="Enter the 6-digit OTP code">
                <button type="submit">Verify OTP</button>
            </form>
        <?php else: ?>
            <form method="post" autocomplete="off" novalidate>
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" minlength="8" required placeholder="Enter new password">
                <button type="submit">Set Password</button>
            </form>
        <?php endif; ?>

        <a href="forgot_password.php" class="back-link">← Back to Forgot Password</a>
    </div>
</body>
</html>
