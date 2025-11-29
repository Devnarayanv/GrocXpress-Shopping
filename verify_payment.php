<?php
session_start();
include "includes/db_connect.php";
require('vendor/autoload.php');

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$keyId = 'YOUR_TEST_KEY_ID';
$keySecret = 'YOUR_TEST_KEY_SECRET';

$success = false;

if (
    !empty($_POST['razorpay_payment_id']) &&
    !empty($_POST['razorpay_order_id']) &&
    !empty($_POST['razorpay_signature'])
) {
    $api = new Api($keyId, $keySecret);
    try {
        $attributes = [
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];
        $api->utility->verifyPaymentSignature($attributes);
        $success = true;

        // Mark order paid: record payment, clear cart, etc.
        $user_id = $_SESSION['user_id'];
        
        // Calculate total
        $cartq = mysqli_query($conn, "SELECT SUM(products.price * cart.quantity) as total
                                      FROM cart JOIN products ON cart.product_id = products.product_id
                                      WHERE cart.user_id = $user_id");
        $cartrow = mysqli_fetch_assoc($cartq);
        $total = floatval($cartrow['total']);

        // Use transaction to ensure atomicity and prevent oversell
        mysqli_begin_transaction($conn);
        $ok = true;

        // Insert order record
        if (!mysqli_query($conn, "INSERT INTO orders (user_id, total_amount, status) VALUES ($user_id, $total, 'paid')")) {
            $ok = false;
        }
        $order_id = mysqli_insert_id($conn);

        // Insert order_items and decrement stock
        $items = mysqli_query($conn, "SELECT product_id, quantity FROM cart WHERE user_id = $user_id");
        while($item = mysqli_fetch_assoc($items)) {
            $pid = intval($item['product_id']);
            $qty = intval($item['quantity']);
            // Check stock (lock row)
            $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE product_id=$pid FOR UPDATE"));
            $available = $prod ? intval($prod['stock']) : 0;
            if ($available < $qty) { $ok = false; break; }

            if (!mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity) VALUES ($order_id, $pid, $qty)")) { $ok = false; break; }

            if (!mysqli_query($conn, "UPDATE products SET stock=stock - $qty WHERE product_id=$pid")) { $ok = false; break; }
        }

        if ($ok) {
            mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");
            mysqli_commit($conn);
        } else {
            mysqli_rollback($conn);
            echo "<div style='text-align:center;margin-top:50px;color:red;'><h2>Order failed - insufficient stock for one or more items.</h2><a href='cart.php' class='animated-btn'>Back to cart</a></div>";
            exit();
        }

        // Display success message
        echo "<div style='text-align: center; margin-top: 50px;'>
                <h2 style='color: green;'>Payment successful!</h2>
                <p>Your order has been placed successfully.</p>
                <a href='main.php' class='animated-btn'>Shop more</a>
              </div>";
    } catch(SignatureVerificationError $e) {
        $success = false;
        echo "<div style='text-align: center; margin-top: 50px;'>
                <h2 style='color: red;'>Signature Verification Failed: " . htmlspecialchars($e->getMessage()) . "</h2>
                <a href='checkout.php' class='animated-btn'>Try Again</a>
              </div>";
    }
} else {
    echo "<div style='text-align: center; margin-top: 50px;'>
            <h2 style='color: red;'>Payment details not found.</h2>
            <a href='checkout.php' class='animated-btn'>Go back to Checkout</a>
          </div>";
}
?>
