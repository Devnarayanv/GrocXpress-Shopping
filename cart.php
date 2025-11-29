<?php
session_start();
include "includes/db_connect.php";
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Handle removal of items
if (isset($_GET['remove'])) {
    $cid = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM cart WHERE cart_id=$cid AND user_id=$user_id");
}

// Handle update quantity
if (isset($_POST['update']) && !empty($_POST['qty'])) {
    foreach ($_POST['qty'] as $cid => $qty) {
    $qty = max(1, intval($qty));
    // Check stock for this cart item
    $cartRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT product_id FROM cart WHERE cart_id=$cid AND user_id=$user_id"));
    if ($cartRow) {
      $pid = intval($cartRow['product_id']);
      $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE product_id=$pid LIMIT 1"));
      $available = $prod ? intval($prod['stock']) : 0;
      if ($qty > $available) {
        // cap to available stock
        $qty = $available;
        $_SESSION['cart_err'] = "Quantity for some items reduced to available stock.";
      }
    }
    mysqli_query($conn, "UPDATE cart SET quantity=$qty WHERE cart_id=$cid AND user_id=$user_id");
    }
}

// Fetch cart items
$q = "SELECT cart.cart_id, cart.quantity, products.* FROM cart JOIN products ON cart.product_id=products.product_id WHERE cart.user_id=$user_id";
$res = mysqli_query($conn, $q);

// Calculate total
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - GrocXpress</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/GrocXpress/assets/style.css">
  <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
    :root {
    --blue: #667eea;
    --blue-dark: #5a67d8;
    --text: #2d3748;
    --box-bg: #fff;
    --light: #fff;
    --danger: #e74c3c;
    --success: #15b374;
    --muted: #a0aec0;
    --border: #c3d0f5;
    --input-bg: #eef2ff;
    --table-bg: #eaf0ff;
}
body {
    background: var(--light);
    color: var(--text);
    font-family: 'Segoe UI', Arial, sans-serif;
}
.cart-wrap {
    max-width:890px;
    margin: 42px auto 0 auto;
    background: var(--box-bg);
    border-radius: 22px;
    box-shadow: 0 8px 38px #667eea31,0 2px 8px #0001;
    border: 2px solid var(--border);
    padding: 2rem 1.4rem 2.0rem 1.4rem;
}
.cart-header {
    text-align: center;
    font-size: 2rem;
    letter-spacing:.03em;
    color: var(--blue);
    font-weight: 900;
    margin-bottom: 26px;
}
.cart-table {
    width: 100%;
    background: var(--table-bg);
    border-radius: 12px;
    box-shadow: 0 1.5px 10px #667eea13;
    overflow: hidden;
    border-collapse: collapse;
}
.cart-table th, .cart-table td {
    padding: 17px 10px;
    text-align: center;
    border-bottom: 1.5px solid #c3d0f5;
    font-size: 1.08rem;
}
.cart-table th {
    background: var(--blue);
    color: #fff;
    font-weight:700;
    font-size: 1.07rem;
    letter-spacing:.02em;
}
.cart-table tr:last-child td { border-bottom: none; }
.cart-table img {
    width:62px; height:62px; object-fit:cover;
    border-radius:8px; box-shadow:0 2px 7px #f7faff;
}
input[type="number"] {
    width: 52px;
    padding: 7px 7px;
    border: 1.3px solid #c3d0f5;
    border-radius: 8px;
    background: var(--input-bg);
    font-size: .99rem;
    text-align: center;
    transition: border-color 0.22s;
}
input[type="number"]:focus { border-color: var(--blue); }
.animated-btn, .btn-main {
    background: var(--blue);
    color: #fff;
    border: none;
    padding: 11px 20px;
    border-radius: 33px;
    font-size: 1.08rem;
    font-weight:700;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.22s, transform 0.14s;
    letter-spacing: .025em;
    margin: 3px;
    outline: none;
    box-shadow: 0 1.5px 8px #c3d0f573;
}
.animated-btn:hover, .btn-main:hover {
    background: var(--blue-dark);
    transform: scale(1.05);
}
.total-section {
    text-align: center;
    margin: 2rem 0 0 0;
    background: #eaf0ff;
    border-radius: 12px;
    padding: 1.2rem 0 .6rem 0;
    box-shadow:0 1.5px 10px #667eea13;
}
.total-section strong {
    font-size: 1.45rem;
    color: var(--blue);
}
.button-group {
    display: flex;
    justify-content: center;
    gap: 1.3rem;
    margin-top: 1.2rem;
    flex-wrap:wrap;
}
.empty {padding:2.1rem 0; text-align:center;}
.empty span {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--muted);
    letter-spacing:.05em;
}
@media(max-width:1050px){
    .cart-wrap{max-width:100%;}
    .cart-table th,.cart-table td{font-size:.99rem}
}
@media(max-width:700px){
    .cart-wrap{padding:.6rem;}
    .cart-table th,.cart-table td{padding:10px 3px;}
    .cart-table img{width:42px;height:42px;}
    .cart-header{font-size:1.4rem;}
}

    </style>
</head>
<body>
  <div class="cart-wrap">
    <div class="cart-header">
      <i class="fas fa-shopping-cart"></i> My Cart
    </div>
    <form method="post" id="cart-form" autocomplete="off">
      <table class="cart-table">
        <tr>
          <th>Image</th>
          <th>Name</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
          <th>Remove</th>
        </tr>
        <?php if(mysqli_num_rows($res)==0): ?>
          <tr><td colspan="6" class="empty"><span>Your cart is empty.</span></td></tr>
        <?php endif; ?>
        <?php while($p = mysqli_fetch_assoc($res)):
          $subtotal = $p['quantity'] * $p['price']; $total += $subtotal; ?>
          <tr>
            <td><img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td>&#8377;<?php echo number_format($p['price'], 2); ?></td>
            <td>
              <input type="number" name="qty[<?php echo $p['cart_id']; ?>]" value="<?php echo $p['quantity']; ?>" min="1" max="<?php echo intval($p['stock'] ?? 0); ?>" onchange="updateSubtotal(this, <?php echo $p['price']; ?>)">
              <div style="font-size:.85rem;color:#666;margin-top:6px;">Available: <?php echo intval($p['stock'] ?? 0); ?></div>
            </td>
            <td class="subtotal" id="subtotal-<?php echo $p['cart_id']; ?>">&#8377;<?php echo number_format($subtotal, 2); ?></td>
            <td>
              <a href="cart.php?remove=<?php echo $p['cart_id']; ?>" class="animated-btn" title="Remove"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
      <div class="total-section">
        <strong>Total: &#8377;<span id="total"><?php echo number_format($total, 2); ?></span></strong>
        <div class="button-group">
          <a href="main.php" class="btn-main"><i class="fas fa-arrow-left"></i> Back to Shop</a>
          <button type="submit" name="update" class="btn-main"><i class="fas fa-redo"></i> Update Quantities</button>
          <a href="checkout.php" class="btn-main"><i class="fas fa-credit-card"></i> Proceed to Checkout</a>
        </div>
      </div>
    </form>
  </div>
  <script>
      function updateSubtotal(input, price) {
          const quantity = input.value;
          const cartId = input.name.match(/\d+/)[0];
          const subtotal = quantity * price;
          document.getElementById('subtotal-' + cartId).innerHTML = '&#8377;' + subtotal.toFixed(2);

          // Update total
          let total = 0;
          const subtotals = document.querySelectorAll('.subtotal');
          subtotals.forEach(function(sub) {
              total += parseFloat(sub.innerHTML.replace('&#8377;', '').replace(',', ''));
          });
          document.getElementById('total').innerHTML = total.toFixed(2);
      }
  </script>
</body>
</html>
