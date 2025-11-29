<?php
session_start();
include "includes/db_connect.php";

// Block admin from accessing user page
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: admin/admin_dashboard.php");
    exit();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cart item count and user avatar
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $cartq = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id=$uid");
    $cartrow = $cartq ? mysqli_fetch_assoc($cartq) : ['total'=>0];
    $cart_count = $cartrow['total'] ?? 0;
    // Avatar (profile photo or UI avatar)
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, profile_image FROM users WHERE user_id=$uid"));
    $avatar = ($user['profile_image'] && file_exists($user['profile_image']))
        ? $user['profile_image']
        : "https://ui-avatars.com/api/?name=" . urlencode($user['name']);
} else {
    $avatar = "https://ui-avatars.com/api/?name=GrocXpress";
}

// Search in electronics
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search_query']);
    $products = mysqli_query($conn, "SELECT * FROM products WHERE LOWER(category)='electronics' AND name LIKE '%$search_query%'");
} else {
    $products = mysqli_query($conn, "SELECT * FROM products WHERE LOWER(category)='electronics'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronics - GrocXpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
  <STYLE>
    :root {
    --main-bg: #fff;
    --box-bg: #fff;
    --primary: #667eea;
    --primary-dark: #5a67d8;
    --side-bg: #eef2ff;
    --text: #2d3748;
    --danger: #e74c3c;
    --success: #27ae60;
    --muted: #a0aec0;
}
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif;}
body { background: var(--main-bg); color: var(--text);}
header { background: var(--box-bg); box-shadow: 0 2px 10px #667eea11; position: sticky; top: 0; z-index: 100;}
.top-bar { display: flex; justify-content: space-between; align-items: center; padding: 17px 5%; max-width: 1400px; margin: 0 auto;}
.logo-section { display: flex; align-items: center; gap: 14px;}
.logo { height: 40px; width: auto;}
.logo-text h1 { font-size: 2rem; font-weight: 700; color: var(--primary-dark);}
.logo-text p { font-size: .97rem; color: #667eea;}
.search-bar { flex: 1; max-width: 440px; margin: 0 2vw; }
.search-bar form { display: flex; }
.search-bar input {
    flex: 1; padding: 11px 17px; border: 1.5px solid #c3d0f5;
    border-radius: 25px 0 0 25px; font-size: 1.02rem; background: #eaf0ff;
}
.search-bar button {
    background: var(--primary); color: white; border: none;
    border-radius: 0 25px 25px 0; padding: 0 19px; font-size: 1.15rem;
    cursor: pointer; transition: background 0.18s;
}
.search-bar button:hover { background: var(--primary-dark);}
.user-nav { display: flex; gap: 23px; align-items: center;}
.nav-icon { color: var(--primary); font-size: 1.4rem; position: relative;}
.cart-count {
    position: absolute; top: -10px; right: -10px; background: var(--primary-dark);
    color: white; border-radius: 50%; width: 19px; height: 19px;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; font-weight: bold;
    box-shadow: 0 2px 8px #667eea22;
}
.profile-avatar {
    width:32px; height:32px; border-radius:50%; object-fit:cover;
    margin-right:8px; vertical-align:middle; background:#f4f7fa; box-shadow:0 1px 3px #c3d0f5;
    border:2px solid #c3d0f5; display:inline-block;
}
.user-dropdown { position: relative; display: inline-block; }
.user-btn {
    background: transparent; border: none; color: var(--primary);
    display: flex; align-items: center; gap: 7px; cursor: pointer;
    font-size: 1.04rem; font-weight: 600;
}
.user-dropdown .dropdown-content { display: none; }
.user-dropdown.show .dropdown-content { display: block; }
.dropdown-content {
    position: absolute; right: 0; background: var(--box-bg);
    min-width: 170px; box-shadow: 0 8px 18px #667eea10;
    z-index: 11; border-radius: 10px; margin-top: 4px;
    padding: 8px 0;
}
.dropdown-content a {
    color: var(--text); padding: 11px 17px; text-decoration: none; display: block;
    font-size: 1rem; border-radius: 8px; margin: 2px 0;
    transition: background 0.14s, color 0.13s;
}
.dropdown-content a:hover { background: #eef2ff; color: var(--primary); }
.main-nav {
    background: var(--primary);
    padding: 9px 6vw;
    display: flex;
    justify-content: center;
    gap: 1.4rem;
}
.main-nav a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    padding: 11px 22px;
    border-radius: 6px;
    transition: background 0.19s, transform 0.18s;
    font-size: 1.06rem;
    letter-spacing: .016em;
}
.main-nav a:hover, .main-nav a.active {
    background: var(--primary-dark);
    color: #fff;
    transform: scale(1.08);
}
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2rem;
    padding: 2.2rem 5vw 2.7rem 5vw;
    max-width: 1400px;
    margin: 0 auto;
}
.product-card {
    background: var(--box-bg);
    border-radius: 16px;
    box-shadow: 0 4px 32px #667eea22;
    text-align: center;
    padding: 1.23rem .92rem .7rem .92rem;
    transition: transform 0.21s, box-shadow 0.23s;
    border: 1.7px solid #c3d0f5;
}
.product-card:hover {
    transform: translateY(-8px) scale(1.021);
    box-shadow: 0 14px 46px #667eea59;
    border-color: var(--primary);
}
.product-image { width: 100%; height: 145px; object-fit: cover;
    border-radius: 12px; cursor: pointer; background: #eaf0ff;}
.product-card h3 { margin: 1rem 0 0.5rem 0; font-size: 1.15rem; color: var(--primary-dark);}
.product-card p { font-size: 1rem; color: var(--primary);}
.product-actions {
    display: flex; align-items: center; justify-content: center; gap: 14px; margin-top: .9em;
}
.buy-now-btn {
    background: var(--success);
    color: white;
    border: none;
    padding: 9px 22px;
    border-radius: 7px;
    font-size: 1.04rem;
    cursor: pointer;
    transition: background 0.19s;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
}
.buy-now-btn:hover { background: #138048; }
.cart-icon, .wishlist-icon {
    display: flex; align-items: center; justify-content: center;
    padding: 0.47rem; font-size: 1.3rem;
    border-radius: 50%; text-decoration: none;
    margin: 0;
}
.cart-icon { color: var(--primary); transition: color 0.2s, background 0.17s;}
.cart-icon:hover { color: #fff; background: var(--primary);}
.wishlist-icon { color: var(--danger); transition: color 0.2s, background 0.18s;}
.wishlist-icon:hover { color: #fff; background: var(--danger);}
@media (max-width: 1050px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 750px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .product-grid { grid-template-columns: repeat(1, 1fr); } }
</STYLE>
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="logo-section">
                <img src="assets/logo.png" alt="GrocXpress Logo" class="logo">
                <div class="logo-text">
                    <h1>GrocXpress</h1>
                    <p>Your one-stop shop for groceries & electronics</p>
                </div>
            </div>
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search_query" placeholder="Search electronics..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" name="search"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="user-nav">
                <a href="wishlist.php" class="nav-icon" title="Wishlist"><i class="fas fa-heart wishlist-icon"></i></a>
                <a href="cart.php" class="nav-icon" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </a>
                <button id="theme-toggle" title="Toggle theme" style="margin-left:8px;"><i class="fas fa-moon"></i></button>
                <div class="user-dropdown">
                    <button class="user-btn" type="button">
                        <img src="<?php echo htmlspecialchars($avatar); ?>" class="profile-avatar" alt="Profile">
                        <span>Account</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="orders.php"><i class="fas fa-box"></i> Orders</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <a href="main.php">Home</a>
            <a href="groceries.php">Groceries</a>
            <a href="electronics.php" class="active">Electronics</a>
            <a href="deals.php">Hot Deals</a>
            <a href="contact.php">Contact</a>
        </nav>
    </header>
    <div class="product-grid">
        <?php while($p = mysqli_fetch_assoc($products)): ?>
            <div class="product-card">
                <a href="product.php?id=<?php echo $p['product_id']; ?>">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" class="product-image" alt="<?php echo htmlspecialchars($p['name']); ?>">
                </a>
                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                <p>&#8377;<?php echo htmlspecialchars($p['price']); ?></p>
                <div class="product-actions">
                    <a href="product.php?id=<?php echo $p['product_id']; ?>" class="buy-now-btn">Buy Now</a>
                    <a href="add_cart.php?id=<?php echo $p['product_id']; ?>" class="cart-icon" title="Add to Cart">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <a href="add_wishlist.php?id=<?php echo $p['product_id']; ?>" class="wishlist-icon" title="Add to Wishlist">
                        <i class="fas fa-heart"></i>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <script>
    // User dropdown click toggle
    document.querySelectorAll('.user-dropdown > .user-btn').forEach(btn => {
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            let parent = btn.parentElement;
            parent.classList.toggle('show');
            document.querySelectorAll('.user-dropdown').forEach(function(dd){
                if(dd!==parent) dd.classList.remove('show');
            });
        });
    });
    document.addEventListener('click', function(){
        document.querySelectorAll('.user-dropdown').forEach(function(dd){
            dd.classList.remove('show');
        });
    });
    </script>
</body>
</html>
