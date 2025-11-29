<?php
session_start();
include "includes/db_connect.php";
if(!isset($_SESSION['user_id'])) {echo "LOGIN"; exit;}
$uid = $_SESSION['user_id'];
$pid = intval($_POST['id']);
$res = mysqli_query($conn, "SELECT * FROM wishlist WHERE user_id=$uid AND product_id=$pid");
if(!mysqli_num_rows($res)) {
    mysqli_query($conn, "INSERT INTO wishlist (user_id, product_id) VALUES ($uid, $pid)");
}
echo "ADDED";
?>
