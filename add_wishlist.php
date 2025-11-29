<?php
session_start();
include "includes/db_connect.php";
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    $check = mysqli_query($conn, "SELECT * FROM wishlist WHERE user_id=$user_id AND product_id=$pid");
    if (!mysqli_fetch_assoc($check)) {
        mysqli_query($conn, "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $pid)");
    }
}
header("Location: wishlist.php");
?>
