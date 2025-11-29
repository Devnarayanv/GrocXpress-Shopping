<?php
session_start();
include "includes/db_connect.php";
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Remove or move to cart
if (isset($_GET['remove'])) {
    $wid = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM wishlist WHERE wishlist_id=$wid AND user_id=$user_id");
}
if (isset($_GET['cart'])) {
    $pid = intval($_GET['cart']);
    // Check stock before moving to cart
    $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE product_id=$pid LIMIT 1"));
    $available = $prod ? intval($prod['stock']) : 0;
    $exists = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$pid");
    if (!mysqli_fetch_assoc($exists)) {
        if ($available < 1) {
            $_SESSION['cart_err'] = 'Product is out of stock.';
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $pid, 1)");
        }
    }
    mysqli_query($conn, "DELETE FROM wishlist WHERE user_id=$user_id AND product_id=$pid");
}

// Show wishlist
$q = "SELECT wishlist.wishlist_id, products.* FROM wishlist JOIN products ON wishlist.product_id=products.product_id WHERE wishlist.user_id=$user_id";
$res = mysqli_query($conn, $q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Wishlist - GrocXpress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
     :root {
        --blue: #667eea;
        --blue-dark: #5a67d8;
        --text: #2d3748;
        --box-bg: #fff;
        --input-bg: #eef2ff;
        --muted: #a0aec0;
        --border: #c3d0f5;
}
body {
    background: var(--box-bg);
    color: var(--text);
    font-family: 'Segoe UI', Arial, sans-serif;
}
.wishlist-wrap {
    max-width: 820px;
    margin: 45px auto 0 auto;
    background: var(--box-bg);
    border-radius: 22px;
    box-shadow: 0 8px 38px #667eea31,0 2px 8px #00000014;
    border: 2px solid var(--border);
    padding: 2rem 1.1rem 2.3rem 1.1rem;
}
.wishlist-header {
    text-align: center;
    font-size: 2rem;
    color: var(--blue);
    font-weight: 900;
    margin-bottom: 26px;
    letter-spacing: .03em;
}
.wishlist-table {
    width: 100%;
    background: #eaf0ff;
    border-radius: 13px;
    box-shadow: 0 1.5px 10px #667eea13;
    overflow: hidden;
    border-collapse: collapse;
    margin-bottom: 18px;
}
.wishlist-table th, .wishlist-table td {
    padding: 16px 10px;
    text-align: center;
    border-bottom: 1.5px solid #c3d0f5;
    font-size: 1.06rem;
}
.wishlist-table th {
    background: var(--blue);
    color: #fff;
    font-weight: 700;
    font-size: 1.05rem;
}
.wishlist-table tr:last-child td { border-bottom: none; }
.wishlist-table img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 2px 7px #eaf0ff;
}
.wishlist-table .name {
    font-weight: 700;
    color: var(--text);
    font-size: 1.07rem;
}
.wishlist-table .price {
    color: var(--blue);
    font-weight: 600;
    font-size: 1.05rem;
}
.animated-btn, .btn-main {
    background: var(--blue);
    color: #fff;
    border: none;
    padding: 10px 17px;
    border-radius: 33px;
    font-size: 1.01rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.21s, transform 0.13s;
    letter-spacing: .02em;
    margin: 3px;
    box-shadow: 0 1.5px 8px #c3d0f573;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.animated-btn:hover, .btn-main:hover {
    background: var(--blue-dark);
    transform: scale(1.06);
}
.empty-msg {
    padding: 2.3rem 0 1.9rem 0;
    font-size: 1.21rem;
    color: var(--muted);
    font-weight: 700;
    background: #eef2ff;
    border-radius: 17px;
    box-shadow: 0 2px 11px #667eea17;
    margin:38px 0;
    letter-spacing:.015em;
}
.back-link {
    display: block;
    text-align: center;
    margin: 2.5rem 0 2rem 0;
    font-size: 1.08rem;
    color: var(--blue);
    text-decoration: none;
    font-weight: 700;
    letter-spacing: .01em;
    transition: color .16s;
}
.back-link:hover {
    color: #2d3748;
    text-decoration: underline;
}
@media (max-width: 950px) {
    .wishlist-wrap {max-width: 99vw;}
}
@media (max-width: 700px) {
    .wishlist-wrap {padding: .5rem;}
    .wishlist-table th, .wishlist-table td { font-size: 0.94rem; padding:10px 3px;}
    .wishlist-table img { width: 44px; height: 44px; }
    .wishlist-header { font-size: 1.5rem;}
}
@media (max-width: 480px) {
    .wishlist-header { font-size: 1.15rem; margin-bottom: 12px;}
    .wishlist-table { font-size: .91rem;}
    .empty-msg { font-size: 1.01rem;}
}

    </style>
</head>
<body>
  <div class="wishlist-wrap">
    <div class="wishlist-header">
      <i class="fas fa-heart"></i> My Wishlist
    </div>
    <table class="wishlist-table">
      <tr>
        <th>Image</th>
        <th>Name</th>
        <th>Price</th>
        <th>Move to Cart</th>
        <th>Remove</th>
      </tr>
      <?php if(mysqli_num_rows($res)==0): ?>
      <tr><td colspan="5" class="empty-msg"><i class="fas fa-box-open"></i> Your wishlist is empty.</td></tr>
      <?php endif; ?>
      <?php while($p = mysqli_fetch_assoc($res)): ?>
      <tr>
        <td><img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"></td>
        <td class="name"><?php echo htmlspecialchars($p['name']); ?></td>
        <td class="price">&#8377;<?php echo number_format($p['price'], 2); ?></td>
        <td>
          <a class="animated-btn" href="wishlist.php?cart=<?php echo $p['product_id']; ?>">
            <i class="fas fa-cart-arrow-down"></i> Move to Cart
          </a>
        </td>
        <td>
          <a class="animated-btn" href="wishlist.php?remove=<?php echo $p['wishlist_id']; ?>">
            <i class="fas fa-trash"></i> Remove
          </a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
    <a href="main.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Shop</a>
  </div>
</body>
</html>
