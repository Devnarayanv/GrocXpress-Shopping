<?php
session_start();
include "includes/db_connect.php";

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit();
}

// Collect Razorpay payment details from Razorpay success handler
$razorpay_payment_id = $_GET['pid'] ?? '';
$razorpay_order_id   = $_GET['oid'] ?? '';
$razorpay_signature  = $_GET['sign'] ?? '';

if (!$razorpay_payment_id || !$razorpay_order_id || !$razorpay_signature) {
    echo "<h2>Payment Failed or Invalid Request.</h2>";
    exit();
}

// (Optional but recommended for real systems)
// Verify signature using Razorpay PHP SDK (for demo, skipping)

$user_id = $_SESSION['user_id'];

// 1. Insert order into orders table
// 2. Insert each cart item into order_items
// 3. Clear cart

// -- Example implementation --
$total = 0;
$cart_items = mysqli_query($conn, "SELECT cart.*, products.price FROM cart JOIN products ON cart.product_id=products.product_id WHERE cart.user_id=$user_id");
foreach ($cart_items as $item) { $total += $item['quantity'] * $item['price']; }

if ($total > 0) {
    // Use transaction to insert order, order_items and decrement stock safely
    mysqli_begin_transaction($conn);
    $ok = true;
    $safePaymentId = mysqli_real_escape_string($conn, $razorpay_payment_id);
    $safeOrderId = mysqli_real_escape_string($conn, $razorpay_order_id);
    $order_sql = "INSERT INTO orders (user_id, order_date, total_amount, status, razorpay_payment_id, razorpay_order_id) VALUES ($user_id, NOW(), $total, 'Paid', '$safePaymentId', '$safeOrderId')";
    if (!mysqli_query($conn, $order_sql)) { $ok = false; }
    $order_id = mysqli_insert_id($conn);

    foreach ($cart_items as $item) {
        $pid = intval($item['product_id']);
        $qty = intval($item['quantity']);
        $price = floatval($item['price']);
        // Lock and check stock
        $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE product_id=$pid FOR UPDATE"));
        $available = $prod ? intval($prod['stock']) : 0;
        if ($available < $qty) { $ok = false; break; }
        if (!mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $qty, $price)")) { $ok = false; break; }
        if (!mysqli_query($conn, "UPDATE products SET stock=stock - $qty WHERE product_id=$pid")) { $ok = false; break; }
    }

    if ($ok) {
        mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id");
        mysqli_commit($conn);
    } else {
        mysqli_rollback($conn);
        echo "<div style='text-align:center;margin-top:50px;color:red;'><h2>Order could not be completed - insufficient stock.</h2><a href='cart.php' class='animated-btn'>Back to cart</a></div>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success - GrocXpress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {font-family:sans-serif; background:#fff6f7; text-align:center;}
        .success {
            margin: 70px auto 30px auto; background: #fff; max-width: 420px; border-radius:18px;
            box-shadow:0 8px 36px #ff4e6424; padding:42px 22px;
        }
        h2 {color:#27ae60;}
        .trx {margin-top:14px; color:#888;}
        .next-btn {
            margin-top: 28px; padding: 13px 35px;
            background: #ff4e64; color: #fff;
            border:none; border-radius:33px; font-size: 1.14rem; font-weight:700; text-decoration:none;
            display:inline-block; transition:background .17s;
        }
        .next-btn:hover {background: #d32f2f;}
    </style>
</head>
<body>
    <div class="success">
        <h2><i class="fas fa-check-circle"></i> Payment Successful!</h2>
        <div class="trx">
            <b>Payment ID:</b> <?=htmlspecialchars($razorpay_payment_id)?><br>
            <b>Order ID:</b> <?=htmlspecialchars($razorpay_order_id)?>
        </div>
        <p style="font-size:1.15em;line-height:1.5;margin:21px 0 8px 0;">
            Thank you for your purchase!<br>
            Your order has been placed and a confirmation email will be sent to you soon.
        </p>
        <a href="orders.php" class="next-btn">View Your Orders</a>
        <br><br>
        <a href="main.php" class="next-btn" style="background:#27ae60;">Shop More</a>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
</body>
</html>
