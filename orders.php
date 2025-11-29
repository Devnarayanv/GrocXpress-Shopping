<?php
session_start();
include "includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch all orders for the user, most recent first
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id=$user_id ORDER BY order_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Orders - GrocXpress</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background:#eef2ff; color:#2d3748; }
.wrap { max-width:850px; margin:43px auto; background:#fff; border-radius:18px; box-shadow:0 8px 32px #667eea24;padding:36px 3vw 2.5vw 3vw; }
h2 { color: #667eea; margin-bottom: 2.4em; font-size:2rem; text-align:center;}
.orders-table { width:100%; border-collapse:collapse;}
.orders-table th,.orders-table td { padding:16px 10px; text-align:left; font-size:1.09em;}
.orders-table th {background:#667eea; color:#fff; font-weight:700;}
.orders-table tr{transition:background .14s;}
.orders-table tr:hover{background:#eaf0ff;}
.orders-table td.status {font-weight: bold;}
.orders-table td.paid {color: #219150;}
.orders-table td.unpaid, .orders-table td.cancelled {color: #5a67d8;}
.btn {background:#667eea;color:#fff;text-decoration:none; font-weight:700;padding:7px 18px;border-radius:22px;}
.btn:hover{background:#5a67d8;}
@media (max-width:600px){
    .wrap{padding:13px 2vw 1.1vw 2vw;}
    .orders-table th,.orders-table td {font-size:.99em;padding:7px 3px;}
}

    </style>
</head>
<body>
<div class="wrap">
    <h2><i class="fas fa-box"></i> My Orders</h2>
    <?php if(mysqli_num_rows($orders) == 0): ?>
        <p style="text-align:center;">You haven't placed any orders yet.<br>
        <a href="main.php" class="btn">Start Shopping</a></p>
    <?php else: ?>
    <table class="orders-table">
        <tr>
            <th>#Order ID</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Details</th>
        </tr>
        <?php while($order = mysqli_fetch_assoc($orders)): ?>
        <tr>
            <td><?=htmlspecialchars($order['order_id'])?></td>
            <td><?=htmlspecialchars(date("d M Y, h:i A", strtotime($order['order_date'])))?></td>
            <td>₹<?=number_format($order['total_amount'],2)?></td>
            <td class="status <?=strtolower($order['status'])?>">
                <?=htmlspecialchars($order['status'])?>
            </td>
            <td><?=!empty($order['razorpay_payment_id']) ? "<span style='color:#219150'><i class='fas fa-check-circle'></i> Paid</span>" : "<span style='color:#d32f2f'><i class='fas fa-times-circle'></i> Not Paid</span>"?></td>
            <td><a href="order_detail.php?id=<?=$order['order_id']?>" class="btn"><i class="fas fa-receipt"></i> View</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
