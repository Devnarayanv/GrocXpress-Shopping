<?php
session_start();
include "includes/db_connect.php";
// Block admin users from accessing this page
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: admin/admin_dashboard.php");
    exit();
}
// Block not logged-in users from accessing this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Cart item count for nav badge
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $cartq = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id=$uid");
    $cartrow = $cartq ? mysqli_fetch_assoc($cartq) : ['total'=>0];
    $cart_count = $cartrow['total'] ?? 0;
    // Fetch user info for avatar
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, profile_image FROM users WHERE user_id = $uid"));
    $avatar = ($user['profile_image'] && file_exists($user['profile_image']))
      ? $user['profile_image']
      : "https://ui-avatars.com/api/?name=" . urlencode($user['name']);
} else {
    $avatar = "https://ui-avatars.com/api/?name=GrocXpress";
}
// Search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search_query']);
    $products = mysqli_query($conn, "SELECT * FROM products WHERE name LIKE '%$search_query%'");
} else {
    $products = mysqli_query($conn, "SELECT * FROM products");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GrocXpress - Shop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    body {
      background: #f8fafc;
      color: #2d3748;
      line-height: 1.6;
    }

    /* Top Header */
    .top-header {
      background: #2d3748;
      color: #e2e8f0;
      font-size: 0.85rem;
      padding: 10px 5%;
      text-align: center;
      border-bottom: 1px solid #4a5568;
    }

    /* Main Header */
    header {
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 5%;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      border-bottom: 1px solid #e2e8f0;
    }

    .logo {
      font-size: 2rem;
      color: #667eea;
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    /* Search Bar */
    .search-bar form {
      display: flex;
      align-items: center;
    }

    .search-bar input {
      padding: 12px 16px;
      border: 2px solid #e2e8f0;
      border-right: none;
      width: 300px;
      border-radius: 12px 0 0 12px;
      outline: none;
      font-size: 1rem;
      background: #fff;
      transition: border-color 0.2s ease;
    }

    .search-bar input:focus {
      border-color: #667eea;
    }

    .search-bar button {
      background: #667eea;
      border: 2px solid #667eea;
      color: white;
      padding: 12px 18px;
      border-radius: 0 12px 12px 0;
      cursor: pointer;
      font-size: 1rem;
      transition: background 0.2s ease;
    }

    .search-bar button:hover {
      background: #5a67d8;
      border-color: #5a67d8;
    }

    /* Navigation Icons */
    .nav-icons {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }

    .nav-icons a,
    .nav-icons button {
      color: #4a5568;
      text-decoration: none;
      position: relative;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.2rem;
      padding: 8px;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .nav-icons a:hover,
    .nav-icons button:hover {
      background: #f7fafc;
      color: #667eea;
    }

    .cart-count {
      position: absolute;
      top: -2px;
      right: -2px;
      background: #667eea;
      color: white;
      font-size: 0.7rem;
      border-radius: 50%;
      padding: 2px 6px;
      min-width: 18px;
      text-align: center;
      font-weight: 600;
    }

    /* Account Dropdown */
    .account-dropdown {
      position: relative;
      display: inline-block;
    }

    .account-dropdown .dropdown-content {
      display: none;
    }

    .account-dropdown.show .dropdown-content {
      display: block;
    }

    .dropdown-content {
      position: absolute;
      background-color: #ffffff;
      min-width: 200px;
      border-radius: 12px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
      z-index: 10;
      top: 45px;
      right: 0;
      padding: 8px 0;
      border: 1px solid #e2e8f0;
    }

    .dropdown-content a {
      color: #2d3748;
      padding: 12px 20px;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.2s ease;
    }

    .dropdown-content a:hover {
      background-color: #f0f4ff;
      color: #667eea;
    }

    .dropdown-content a i {
      width: 16px;
      text-align: center;
    }

    .profile-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 8px;
      vertical-align: middle;
      background: #e2e8f0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border: 2px solid #667eea;
      display: inline-block;
    }

    /* Category Tabs */
    .category-tabs {
      text-align: center;
      margin: 0;
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      padding: 1rem 0;
    }

    .cat-tab {
      display: inline-block;
      margin: 0 0.5rem;
      padding: 12px 24px;
      border-radius: 8px;
      background: #f8fafc;
      font-size: 0.95rem;
      color: #4a5568;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s ease;
      border: 2px solid #e2e8f0;
    }

    .cat-tab:hover {
      background: #667eea;
      color: #fff;
      border-color: #667eea;
    }

    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 4rem 2rem;
      text-align: center;
      color: white;
      position: relative;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('https://foodinstitute.com/wp-content/uploads/2023/06/DigitalGroceryGenAI.jpg.webp') no-repeat center center;
      background-size: cover;
      opacity: 0.2;
      z-index: 0;
    }

    .hero > * {
      position: relative;
      z-index: 1;
    }

    .hero h1 {
      font-size: 2.8rem;
      font-weight: 800;
      margin-bottom: 1rem;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero p {
      font-size: 1.2rem;
      margin-bottom: 2rem;
      opacity: 0.9;
    }

    .hero .btn {
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      color: white;
      padding: 14px 32px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255, 255, 255, 0.3);
      transition: all 0.2s ease;
    }

    .hero .btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
    }

    /* Product Section */
    .product-section {
      padding: 3rem 5%;
      background: #f8fafc;
    }

    .product-section h2 {
      font-size: 2rem;
      margin-bottom: 2rem;
      color: #2d3748;
      text-align: center;
      font-weight: 700;
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .product-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1.5rem;
      transition: all 0.2s ease;
      position: relative;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .product-card:hover {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      transform: translateY(-2px);
      border-color: #667eea;
    }

    .product-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    .product-card h3 {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #2d3748;
    }

    .product-card p {
      color: #667eea;
      font-weight: 700;
      font-size: 1.1rem;
      margin-bottom: 1rem;
    }

    .product-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 0.75rem;
    }

    .product-card .btn {
      background: #667eea;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: 600;
      transition: all 0.2s ease;
      border: none;
      cursor: pointer;
      flex: 1;
      text-align: center;
    }

    .product-card .btn:hover {
      background: #5a67d8;
    }

    .product-actions button.icon {
      background: #f0f4ff;
      color: #667eea;
      border: 2px solid #e2e8f0;
      padding: 10px;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.2s ease;
      outline: none;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .product-actions button.icon:hover,
    .product-actions button.icon.success-flash {
      background: #667eea;
      color: #fff;
      border-color: #667eea;
    }

    /* Responsive Design */
    @media (max-width: 900px) {
      .product-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .search-bar input {
        width: 200px;
      }

      .hero h1 {
        font-size: 2.2rem;
      }
    }

    @media (max-width: 650px) {
      header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }

      .nav-icons {
        align-self: flex-end;
        margin-top: -2.5rem;
      }

      .search-bar {
        width: 100%;
      }

      .search-bar input {
        width: calc(100% - 60px);
      }

      .product-grid {
        grid-template-columns: 1fr;
      }

      .top-header {
        padding: 10px 1rem;
      }

      .product-section {
        padding: 2rem 1rem;
      }

      .hero {
        padding: 3rem 1rem;
      }

      .hero h1 {
        font-size: 1.8rem;
      }

      .category-tabs {
        padding: 0.75rem;
      }

      .cat-tab {
        margin: 0.25rem;
        padding: 8px 16px;
        font-size: 0.9rem;
      }
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e0;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #a0aec0;
    }

    /* Animation */
    @keyframes fadein {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .dropdown-content {
      animation: fadein 0.2s ease-out;
    }

    html {
      scroll-behavior: smooth;
    }
  </style>
  <!-- global styles and theme script -->
  <link rel="stylesheet" href="/GrocXpress/assets/style.css">
  <script src="/GrocXpress/assets/script.js" defer></script>
</head>
<body>
  <div class="top-header">
    Best Sellers | New Releases | Today's Deals | Customer Service
  </div>
  <header>
    <div class="logo">GrocXpress</div>
    <div class="search-bar">
      <form method="GET" action="">
        <input type="text" name="search_query" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" name="search"><i class="fas fa-search"></i></button>
      </form>
    </div>
    <div class="nav-icons">
      <a href="cart.php"><i class="fas fa-shopping-cart"></i>
        <span class="cart-count"><?php echo $cart_count; ?></span>
      </a>
  <a href="wishlist.php"><i class="fas fa-heart"></i></a>
      <button id="theme-toggle" title="Toggle theme"><i class="fas fa-moon"></i></button>
      <div class="account-dropdown">
        <button type="button" style="display:flex;align-items:center;gap:8px;font-size:1rem;font-weight:600;background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;transition:background 0.2s ease;">
          <img src="<?php echo htmlspecialchars($avatar); ?>" class="profile-avatar" alt="Profile">
          <span>Account</span>
          <i class="fas fa-angle-down"></i>
        </button>
        <div class="dropdown-content">
          <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
          <a href="orders.php"><i class="fas fa-box"></i> Orders</a>
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </header>
  <nav class="category-tabs">
    <a href="groceries.php" class="cat-tab">Groceries</a>
    <a href="electronics.php" class="cat-tab">Electronics</a>
  </nav>
  <section class="hero">
    <h1>GET STARTED <br> YOUR FAVORITE SHOPPING</h1>
    <p>Electronics & Groceries Delivered Fast</p>
    <a href="#products" class="btn" id="jump-to-products">SHOP NOW</a>
  </section>
  <section class="product-section" id="products">
    <h2>Your everyday needs, from groceries to gadgets.</h2>
    <div class="product-grid">
      <?php while($p = mysqli_fetch_assoc($products)): ?>
      <div class="product-card">
        <a href="product.php?id=<?php echo $p['product_id']; ?>">
          <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
        </a>
        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
        <p>₹<?php echo htmlspecialchars($p['price']); ?></p>
        <div class="product-actions">
          <a href="product.php?id=<?php echo $p['product_id']; ?>" class="btn">Buy Now</a>
          <button class="icon add-cart" data-id="<?php echo $p['product_id']; ?>" title="Add to Cart"><i class="fas fa-shopping-cart"></i></button>
          <button class="icon add-wishlist" data-id="<?php echo $p['product_id']; ?>" title="Add to Wishlist"><i class="fas fa-heart"></i></button>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </section>
  <script>
  // Add to Cart AJAX
  document.querySelectorAll('.add-cart').forEach(btn => {
      btn.addEventListener('click', function(e){
          e.preventDefault();
          var pid = this.getAttribute('data-id');
          fetch('ajax_add_cart.php', {
              method: 'POST',
              headers: {'Content-Type':'application/x-www-form-urlencoded'},
              body: 'id='+encodeURIComponent(pid)
          })
          .then(response => response.text())
      .then(txt => {
        if (txt == 'LOGIN') {
          alert('Please login to add to cart.');
        } else if (txt == 'OUT_OF_STOCK') {
          alert('Sorry, this product is out of stock or you requested more than available.');
        } else {
          document.querySelector('.cart-count').textContent = txt;
          btn.classList.add('success-flash');
          setTimeout(()=>btn.classList.remove('success-flash'), 500);
        }
      });
      });
  });
  // Add to Wishlist AJAX
  document.querySelectorAll('.add-wishlist').forEach(btn => {
      btn.addEventListener('click', function(e){
          e.preventDefault();
          var pid = this.getAttribute('data-id');
          fetch('ajax_add_wishlist.php', {
              method: 'POST',
              headers: {'Content-Type':'application/x-www-form-urlencoded'},
              body: 'id='+encodeURIComponent(pid)
          })
          .then(response => response.text())
          .then(txt => {
              if (txt == 'LOGIN') {
                  alert('Please login to add to wishlist.');
              } else {
                  btn.classList.add('success-flash');
                  setTimeout(()=>btn.classList.remove('success-flash'), 500);
              }
          });
      });
  });
  // Account Dropdown (Click to open, click anywhere else to close)
  document.querySelectorAll('.account-dropdown > button').forEach(btn => {
    btn.addEventListener('click', function(e){
      e.stopPropagation();
      let parent = btn.parentElement;
      parent.classList.toggle('show');
      document.querySelectorAll('.account-dropdown').forEach(function(dd){
        if(dd!==parent) dd.classList.remove('show');
      });
    });
  });
  document.addEventListener('click', function(){
    document.querySelectorAll('.account-dropdown').forEach(function(dd){
      dd.classList.remove('show');
    });
  });
  </script>
</body>
</html>
