<?php
session_start();
include "includes/db_connect.php";
require 'vendor/autoload.php';

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($q)) {
        $otp = rand(100000,999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $user = mysqli_fetch_assoc($q);
        mysqli_query($conn, "UPDATE users SET otp_code='$otp', otp_expiry='$expiry' WHERE user_id={$user['user_id']}");
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply.grocxpress@gmail.com';
        $mail->Password = 'ldhv pzaw gacl enur';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('noreply.grocxpress@gmail.com', 'GrocXpress');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Your GrocXpress OTP";
        $mail->Body = "Your OTP for password reset: <b>$otp</b><br>This code expires in 10 minutes.";

        $mail->send();
        $_SESSION['otp_email'] = $email;
        $msg = "OTP sent! Check your email and enter it below.";
    } else {
        $msg = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Forgot Password - OTP</title>
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
    height: 100vh;
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
  input[type="email"] {
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
  input[type="email"]:focus {
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
  p a {
    color: var(--main-blue);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.15s;
  }
  p a:hover {
    color: var(--main-hover);
    text-decoration: underline;
  }
</style>
</head>
<body>
<div class="card">
  <h1>Forgot Password (OTP)</h1>
  <?php if ($msg): ?>
    <div class="message <?= strpos($msg, 'No account') !== false ? 'error' : 'success' ?>">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>
  <form method="post" autocomplete="off" novalidate>
    <label for="email">Enter your email address:</label>
    <input id="email" type="email" name="email" required placeholder="you@example.com" />
    <button type="submit">Send OTP</button>
  </form>
  <p><a href="verify_otp.php">Already have an OTP? Verify here</a></p>
</div>
</body>
</html>
