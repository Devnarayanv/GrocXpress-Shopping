<?php
session_start();
$thank_you = '';
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['name'], $_POST['email'], $_POST['message'])) {
    include "includes/db_connect.php";
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    $q = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$msg')";
    mysqli_query($conn, $q);
    $thank_you = "Thank you! We'll get back to you soon..";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - GrocXpress</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/GrocXpress/assets/style.css">
  <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
      :root {
        --bg: #fff;
        --red: #ff4e64;
        --red-dark: #d32f2f;
        --box: #fff;
        --input-bg: #fff6f7;
        --text: #181518;
        --muted: #ada9b1;
      }
      body { background: var(--bg); font-family: 'Segoe UI', Arial, sans-serif; color: var(--text);}
      .contact-wrap {
        max-width: 970px;
        margin: 46px auto;
        background: var(--box);
        border-radius: 22px;
        box-shadow: 0 8px 38px #ff4e6433, 0 2px 8px #ff4e6421;
        padding: 2.6rem 2rem 2.5rem 2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 3vw;
      }
      .contact-info, .contact-form { flex:1 1 305px; }
      .contact-info h2 {
        color: var(--red);
        font-size: 2rem;
        margin-bottom: 16px;
        font-weight: 800;
        letter-spacing: .022em;
      }
      .contact-info p { font-size: 1.02rem; margin-bottom: 10px; }
      .contact-info i { color: var(--red); margin-right: 10px; }
      .contact-social a { color: var(--red); font-size: 1.3rem; margin-right: 13px;transition:color 0.13s;}
      .contact-social a:hover { color: var(--red-dark);}
      .contact-info strong { color: var(--red-dark);}
      .contact-form h3 {
        font-size: 1.21rem;
        font-weight:700;
        margin-bottom: 1.2em;
        color: var(--red-dark);
      }
      .contact-form form input, .contact-form form textarea {
        width: 100%; margin-bottom: 17px; font-size: 1rem;
        padding: 11px 13px; border-radius: 8px;
        border: 1.5px solid #ffccd7; background: var(--input-bg);
        color: #222; transition: border 0.16s;
      }
      .contact-form form input:focus, .contact-form form textarea:focus {border-color: var(--red);}
      .contact-form form textarea { min-height: 85px; }
      .contact-form form button {
        background: var(--red);
        color: #fff; font-weight: 800; border: none;
        padding: 13px 0; border-radius: 33px; font-size: 1.12rem;
        width: 100%; transition: background 0.19s;
        margin-top: 7px; letter-spacing: .03em;
        box-shadow: 0 1.5px 7px #ff4e6433;
      }
      .contact-form form button:hover { background: var(--red-dark);}
      .success-msg {
        color: #009a6f; font-weight: 700; margin-bottom: 14px;
        background: #e9fbe9; border-left: 4px solid #27ae60;
        border-radius:7px; padding: 8px 11px;
        font-size: 1.05rem; letter-spacing:.01em;
      }
      .contact-info iframe {
        border-radius: 11px; margin-top: 16px; width: 100%;
        border: none;
      }
      @media(max-width:950px){.contact-wrap{flex-direction:column;padding:1.2rem;}.contact-info,.contact-form{flex:none;}}
    </style>
</head>
<body>
    <header style="background:#fff;box-shadow:0 2px 8px #ff4e6411;padding:1.3rem 0 1rem 0;">
        <div style="max-width:1200px;margin:auto;display:flex;align-items:center;gap:22px;">
            <img src="assets/logo.png" style="height:37px">
            <span style="color:#ff4e64;font-weight:800;font-size:1.5rem;letter-spacing:0.04em;">GrocXpress</span>
        </div>
    </header>
    <div class="contact-wrap">
        <div class="contact-info">
            <h2>Contact Us</h2>
            <p><i class="fas fa-map-marker-alt"></i> 123 Store St, Retail City, RC 10001</p>
            <p><i class="fas fa-phone"></i> (123) 456-7890</p>
            <p><i class="fas fa-envelope"></i> hello@grocxpress.com</p>
            <p style="margin:19px 0 5px 0;">
                <b>Follow us:</b>
                <span class="contact-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </span>
            </p>
            <p style="margin-top:14px;font-size:1rem;color:#888;">
                <strong>Customer Service Hours:</strong><br>
                Mon - Sat: 8:00 am – 8:00 pm <br>
                Sunday: 10:00 am – 4:00 pm
            </p>
            <iframe src="https://maps.google.com/maps?q=123%20Store%20St,%20Retail%20City&t=&z=13&ie=UTF8&iwloc=&output=embed" height="149" allowfullscreen aria-hidden="false" tabindex="0"></iframe>
        </div>
        <div class="contact-form">
            <h3><i class="far fa-envelope"></i> Send Us a Message</h3>
            <?php if($thank_you): ?><div class="success-msg"><?= htmlspecialchars($thank_you); ?></div><?php endif; ?>
            <form method="post" autocomplete="off">
                <input type="text" name="name" placeholder="Your name" required>
                <input type="email" name="email" placeholder="Your email address" required>
                <textarea name="message" placeholder="Type your message..." required></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i> Send Message</button>
            </form>
        </div>
    </div>
</body>
</html>
