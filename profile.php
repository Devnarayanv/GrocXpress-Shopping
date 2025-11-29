<?php
session_start();
include "includes/db_connect.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$otp_msg = "";
$otp_sent_msg = "";

// Fetch user data
$q = mysqli_query($conn, "SELECT * FROM users WHERE user_id=$user_id");
$user = mysqli_fetch_assoc($q);

function mask_email($email) {
    $em   = explode("@",$email);
    $name = implode(str_repeat("*", max(1, floor(strlen($em[0])/2))), array($em[0][0], $em[0][strlen($em[0])-1]));
    return $name . "@" . $em[1];
}

// Handle sending OTP
if (isset($_POST['send_otp'])) {
    $email = $user['email'];
    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    mysqli_query($conn, "UPDATE users SET otp_code='$otp', otp_expiry='$expiry' WHERE user_id=$user_id");

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Set to 2 to debug
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply.grocxpress@gmail.com';
        $mail->Password = 'ldhv pzaw gacl enur';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom('noreply.grocxpress@gmail.com', 'GrocXpress');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your GrocXpress OTP';
        $mail->Body = "Your OTP for password change: <b>$otp</b>. This code expires in 10 minutes.";
        $mail->send();

        $masked_email = mask_email($email);
        $otp_sent_msg = "OTP sent to email address <strong>$masked_email</strong>. Please check your inbox.";
    } catch (Exception $e) {
        $otp_sent_msg = "Mailer Error: " . $mail->ErrorInfo;
    }
}

// Handle OTP verification
if (isset($_POST['verify_otp'])) {
    $entered_otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $q2 = mysqli_query($conn, "SELECT * FROM users WHERE user_id=$user_id AND otp_code='$entered_otp' AND otp_expiry > NOW()");
    if (mysqli_num_rows($q2)) {
        $_SESSION['otp_verified'] = true;
        $otp_msg = "OTP verified! You can now change your password.";
    } else {
        $otp_msg = "Invalid or expired OTP.";
        unset($_SESSION['otp_verified']);
    }
}

// Handle profile update submission
if (isset($_POST['update'])) {
    $new_first = mysqli_real_escape_string($conn, $_POST['first_name']);
    $new_last = mysqli_real_escape_string($conn, $_POST['last_name']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $name_pattern = '/^[A-Z][a-zA-Z]{1,29}$/';

    if (!preg_match($name_pattern, $new_first)) {
        $msg = "First name must start with a capital letter and contain only letters (2-30 chars, no spaces).";
    } elseif (!preg_match($name_pattern, $new_last)) {
        $msg = "Last name must start with a capital letter and contain only letters (2-30 chars, no spaces).";
    } else {
        $updates = "first_name='$new_first', last_name='$new_last', email='$new_email'";

        if (!empty($_POST['password'])) {
            if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
                $msg = "Please verify OTP before changing password.";
            } else {
                $new_pass = md5($_POST['password']);
                $updates .= ", password='$new_pass'";
            }
        }

        if (!$msg && !empty($_FILES['profile_image']['name'])) {
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
            $img_name = basename($_FILES['profile_image']['name']);
            $img_path = "uploads/" . uniqid() . "_" . $img_name;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $img_path)) {
                $updates .= ", profile_image='$img_path'";
            } else {
                $msg = "Profile image upload failed!";
            }
        }

        if (!$msg && mysqli_query($conn, "UPDATE users SET $updates WHERE user_id=$user_id")) {
            $msg = "Profile updated successfully!";
            unset($_SESSION['otp_verified']);
        } elseif (!$msg) {
            $msg = "Error updating profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>My Account - GrocXpress</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="/GrocXpress/assets/style.css">
<script src="/GrocXpress/assets/script.js" defer></script>
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
  margin: 0;
}
.wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.card {
  background: var(--light);
  max-width: 700px;
  width: 100%;
  margin: 40px auto;
  border-radius: 20px;
  box-shadow: 0 8px 32px #667eea35, 0 2px 8px #00000017;
  padding: 2.7rem 2.5rem;
  text-align: center;
  border: 2px solid var(--input-border);
  position: relative;
  overflow: hidden;
}
.card:before {
  content:'';
  position:absolute; left:-80px; top:-90px;
  width:160px;height:160px;
  background: linear-gradient(135deg,#eef2ff 60%,#667eea 100%);
  border-radius:50%; z-index:0;
  opacity:0.3;
}
.card h2 {
  font-weight: 700;
  margin-bottom: 2.1rem;
  font-size: 2rem;
  color: var(--main-blue);
  letter-spacing: 0.04em;
  z-index:1; position:relative;
}
.profile-img {
  margin: 0 auto 25px auto;
  width: 110px; height: 110px;
  object-fit: cover; border-radius: 50%;
  box-shadow: 0 4px 14px #667eea44;
  border: 3px solid #fff;
  background: #eef2ff;
  display: block;
  z-index: 1;
}
form {
  margin-top: 10px;
  text-align: left;
  z-index: 1;
  position: relative;
}
.form-group {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 18px;
}
.form-group > div {
  flex: 1 1 calc(50% - 10px);
}
label {
  font-weight: 600;
  color: var(--main-blue);
  letter-spacing: .01em;
  display: block;
  margin-bottom: 6px;
  font-size: 1.08rem;
}
input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
  width: 100%;
  border: 1.5px solid var(--input-border);
  border-radius: 9px;
  padding: 13px 14px;
  font-size: 1.08rem;
  background: var(--input-bg);
  color: var(--dark);
  transition: border 0.18s;
  box-sizing: border-box;
}
input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
  border-color: var(--main-blue);
  outline: none;
}
input[type="file"] {
  padding: 6px 10px;
  margin-bottom: 20px;
}
.btn-main {
  width: 100%;
  padding: 15px 0;
  background: var(--main-blue);
  color: #fff;
  border: none;
  border-radius: 33px;
  font-weight: 800;
  font-size: 1.14rem;
  box-shadow: 0 2px 18px #667eea40;
  cursor: pointer;
  margin-top: 12px;
  margin-bottom: 10px;
  letter-spacing: .044em;
  text-transform: uppercase;
  transition: background 0.17s;
}
.btn-main:hover {
  background: var(--main-hover);
}
.msg, .otp-msg {
  padding: 13px 15px;
  margin-bottom: 15px;
  font-size: 1.07rem;
  border-radius: 7px;
  font-weight: 700;
  text-align: center;
  letter-spacing: 0.02em;
}
.msg.succ {
  background: #e9fbe9;
  color: var(--success);
  border-left: 4px solid var(--success);
}
.msg.err {
  background: #ffe6e6;
  color: var(--danger);
  border-left: 4px solid var(--danger);
}
.otp-container {
  margin-top: 25px;
  margin-bottom: 30px;
  border-radius: 14px;
  border: 2px solid var(--input-border);
  background: #f7faff;
  padding: 19px 24px;
}
.otp-container label {
  margin-top: 5px;
  margin-bottom: 12px;
}
.otp-container input[type="text"] {
  max-width: 150px;
  display: inline-block;
  margin-right: 12px;
}
@media (max-width: 700px) {
  .form-group > div {
    flex: 1 1 100%;
  }
}
</style>
<script>
function validateProfileForm() {
    let first = document.getElementById('first_name').value.trim();
    let last = document.getElementById('last_name').value.trim();
    let namePattern = /^[A-Z][a-zA-Z]{1,29}$/;
    if (!namePattern.test(first)) {
        alert('First name must start with a capital letter, only letters, 2-30 long, no spaces.');
        return false;
    }
    if (!namePattern.test(last)) {
        alert('Last name must start with a capital letter, only letters, 2-30 long, no spaces.');
        return false;
    }
    return true;
}
</script>
</head>
<body>
<div class="wrap">
 <div class="card">
   <h2><i class="fas fa-user-circle"></i> My Account</h2>

   <?php if ($msg): ?>
   <div class="msg <?= strpos($msg, 'successfully') !== false ? 'succ' : 'err' ?>">
     <?= htmlspecialchars($msg) ?>
   </div>
   <?php endif; ?>

   <?php if ($otp_msg): ?>
   <div class="msg <?= strpos($otp_msg, 'verified') !== false ? 'succ' : 'err' ?>">
     <?= htmlspecialchars($otp_msg) ?>
   </div>
   <?php endif; ?>

   <?php if ($otp_sent_msg): ?>
   <div class="msg succ">
     <?= htmlspecialchars($otp_sent_msg) ?>
   </div>
   <?php endif; ?>

   <form method="post" enctype="multipart/form-data" autocomplete="off" onsubmit="return validateProfileForm()">
     <center>
       <img src="<?= ($user['profile_image'] && file_exists($user['profile_image'])) ? htmlspecialchars($user['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] . ' ' . $user['last_name']) . '&background=eef2ff&color=667eea'; ?>" alt="Profile Image" class="profile-img" />
     </center>

     <div class="form-group">
       <div>
         <label for="first_name">First Name</label>
         <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required pattern="^[A-Z][a-zA-Z]{1,29}$" title="First uppercase, only letters, 2-30 chars, no spaces">
       </div>
       <div>
         <label for="last_name">Last Name</label>
         <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required pattern="^[A-Z][a-zA-Z]{1,29}$" title="First uppercase, only letters, 2-30 chars, no spaces">
       </div>
     </div>

     <label for="email">Email</label>
     <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

     <div class="otp-container">
       <label for="otp"><strong>Verify OTP to Change Password</strong></label>
       <?php if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true): ?>
         <input type="text" id="otp" name="otp" maxlength="6" placeholder="Enter OTP" />
         <button type="submit" name="verify_otp" style="padding: 6px 18px; margin-right: 10px; background: var(--main-blue); color: white; border: none; border-radius: 20px; font-weight: 700;">Verify OTP</button>
         <button type="submit" name="send_otp" style="padding: 6px 18px; background: var(--main-hover); color: white; border: none; border-radius: 20px; font-weight: 700;">Send OTP</button>
       <?php else: ?>
         <div style="padding: 10px; background: #e9fbe9; color: var(--success); border-left: 4px solid var(--success); margin-bottom: 15px;">
           OTP verified! You can now change your password below.
         </div>
       <?php endif; ?>

       <?php if ($otp_msg): ?>
         <div class="msg <?= strpos($otp_msg, 'verified') !== false ? 'succ' : 'err' ?>">
           <?= htmlspecialchars($otp_msg) ?>
         </div>
       <?php endif; ?>

       <?php if ($otp_sent_msg): ?>
         <div class="msg succ">
           <?= htmlspecialchars($otp_sent_msg) ?>
         </div>
       <?php endif; ?>
     </div>

     <label for="password">New Password</label>
     <input type="password" name="password" id="password" placeholder="Leave blank to keep same password" <?= empty($_SESSION['otp_verified']) ? 'disabled' : '' ?> >

     <label for="profile_image">Change Profile Image</label>
     <input type="file" name="profile_image" id="profile_image" accept="image/*">

     <button type="submit" name="update" class="btn-main"><i class="fas fa-save"></i> Update Profile</button>
   </form>

   <a href="main.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Shop</a>
 </div>
</div>
</body>
</html>
