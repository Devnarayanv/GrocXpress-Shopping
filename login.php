<?php
session_start();
include "includes/db_connect.php";
require 'vendor/autoload.php';
$_SESSION['is_admin'] = 0;
unset($_SESSION['user_id']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['email']) && isset($_POST['password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); // Using MD5 is not secure for passwords. Consider using password_hash().
    $q = "SELECT * FROM users WHERE email='$email' AND password='$password' LIMIT 1";
    $r = mysqli_query($conn, $q);
    $user = mysqli_fetch_assoc($r);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['is_admin'] = ($user['is_admin'] == 1) ? 1 : 0;
        $_SESSION['admin_name'] = $user['name'];
        if ($_SESSION['is_admin']) {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: main.php");
        }
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - GrocXpress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #4CAF50; /* Modern Green */
            --primary-dark-green: #388E3C;
            --secondary-blue: #2196F3; /* Complementary Blue */
            --background-light: #f0f2f5; /* Soft light gray */
            --card-background: #ffffff;
            --text-color: #333;
            --heading-color: #2c3e50;
            --border-light: #e0e0e0;
            --shadow-light: rgba(0, 0, 0, 0.08);
            --danger-color: #e74c3c;
            --transition-speed: 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-color);
        }

        .login-container {
            display: flex;
            max-width: 1000px;
            width: 90%;
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: 0 15px 40px var(--shadow-light);
            overflow: hidden;
            min-height: 550px; /* Ensure sufficient height */
        }

        .welcome-section {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-dark-green) 100%);
            color: #fff;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: rotate(45deg);
        }
        .welcome-section::after {
            content: '';
            position: absolute;
            bottom: -70px;
            right: -70px;
            width: 250px;
            height: 250px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: rotate(-30deg);
        }

        .welcome-section h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 800;
            letter-spacing: 1px;
            z-index: 1;
        }

        .welcome-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            z-index: 1;
        }

        .welcome-section .logo-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            z-index: 1;
        }
        .welcome-section .tagline {
            font-size: 1.2rem;
            font-weight: 500;
            margin-top: 1.5rem;
            z-index: 1;
        }

        .login-form-section {
            flex: 1;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-section h2 {
            color: var(--heading-color);
            text-align: center;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 2rem;
            position: relative;
        }
        .login-form-section h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary-green);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .input-field {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            font-size: 1rem;
            color: var(--text-color);
            background-color: #fcfcfc;
            transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
        }

        .input-field:focus {
            border-color: var(--primary-green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        .login-button {
            width: 100%;
            background: var(--primary-green);
            color: #fff;
            font-weight: 700;
            border: none;
            padding: 15px 0;
            border-radius: 30px;
            font-size: 1.15rem;
            transition: all var(--transition-speed);
            margin-top: 1.5rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .login-button:hover {
            background: var(--secondary-blue);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(33, 150, 243, 0.4);
        }

        .forgot-link, .register-link {
            display: block;
            text-align: center;
            color: var(--primary-green);
            font-size: 0.95rem;
            text-decoration: none;
            margin-top: 1rem;
            transition: color var(--transition-speed), text-decoration var(--transition-speed);
        }
        .forgot-link {
             margin-top: 0.8rem;
             text-align: right;
        }
        .forgot-link:hover, .register-link:hover {
            color: var(--primary-dark-green);
            text-decoration: underline;
        }
        .register-link {
            background-color: var(--secondary-blue);
            color: #fff;
            padding: 12px 20px;
            border-radius: 30px;
            margin-top: 2rem;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.2);
            transition: all var(--transition-speed);
        }
        .register-link:hover {
            background-color: var(--primary-dark-green);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .error-message {
            color: var(--danger-color);
            background: #ffebe6;
            border-left: 5px solid var(--danger-color);
            font-weight: 500;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
            font-size: 0.95rem;
            padding: 12px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-10px); }
            40%, 80% { transform: translateX(10px); }
        }

        @media (max-width: 860px) {
            .welcome-section {
                display: none; /* Hide welcome section on smaller screens */
            }
            .login-container {
                width: 90%;
                max-width: 500px;
                min-height: auto;
            }
            .login-form-section {
                padding: 2.5rem 2rem;
            }
            .login-form-section h2 {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }
            .login-button, .register-link {
                padding: 13px 0;
                font-size: 1.05rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 95%;
                margin: 1rem;
            }
            .login-form-section {
                padding: 2rem 1.5rem;
            }
            .login-form-section h2 {
                font-size: 1.6rem;
                margin-bottom: 1.2rem;
            }
            .input-field {
                padding: 12px 15px;
            }
            .login-button, .register-link {
                padding: 12px 0;
                font-size: 1rem;
            }
            .forgot-link, .register-link {
                font-size: 0.9rem;
            }
            .error-message {
                font-size: 0.9rem;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="welcome-section">
            <i class="fas fa-shopping-basket logo-icon"></i>
            <h1>Welcome Back!</h1>
            <p>We've missed you! Log in to continue your seamless shopping experience with GrocXpress.</p>
            <span class="tagline">Your one-stop shop for Groceries & Electronics</span>
        </div>
        <div class="login-form-section">
            <h2>User Login</h2>
            <?php if ($error) : ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email Address.</label>
                    <input type="email" name="email" id="email" class="input-field" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password..</label>
                    <input type="password" name="password" id="password" class="input-field" placeholder="Enter your password" required>
                    <a href="forgot_password.php" class="forgot-link">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>
                <button type="submit" class="login-button"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>
            <a href="register.php" class="register-link">
                <i class="fas fa-user-plus"></i> Don't have an account? Create now
            </a>
        </div>
    </div>
</body>
</html>