<?php
session_start();
include "includes/db_connect.php";
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    // Check available stock
    $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE product_id=$pid LIMIT 1"));
    $available = $prod ? intval($prod['stock']) : 0;
    $q = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$pid");
    if ($row = mysqli_fetch_assoc($q)) {
        $cid = $row['cart_id'];
        $newQty = $row['quantity'] + 1;
        if ($newQty > $available) {
            // Cannot add more than available
            $_SESSION['cart_err'] = 'Not enough stock available.';
        } else {
            mysqli_query($conn, "UPDATE cart SET quantity=quantity+1 WHERE cart_id=$cid");
        }
    } else {
        if ($available < 1) {
            $_SESSION['cart_err'] = 'Product is out of stock.';
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $pid, 1)");
        }
    }
}
header("Location: cart.php");
?>
