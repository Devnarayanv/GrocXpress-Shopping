<?php
session_start();
include "includes/db_connect.php";
if(!isset($_SESSION['user_id'])) {echo "LOGIN"; exit;}
$uid = $_SESSION['user_id'];
$pid = intval($_POST['id']);
$res = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$uid AND product_id=$pid");
// Check stock before adding
$prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE product_id=$pid LIMIT 1"));
$available = $prod ? intval($prod['stock']) : 0;
if(mysqli_num_rows($res)) {
    $row = mysqli_fetch_assoc($res);
    if ($row['quantity'] + 1 > $available) {
        echo 'OUT_OF_STOCK';
        exit;
    }
    mysqli_query($conn, "UPDATE cart SET quantity=quantity+1 WHERE user_id=$uid AND product_id=$pid");
} else {
    if ($available < 1) {
        echo 'OUT_OF_STOCK';
        exit;
    }
    mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid, 1)");
}
$countq = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id=$uid");
$countrow = mysqli_fetch_assoc($countq);
echo $countrow['total'];
?>
