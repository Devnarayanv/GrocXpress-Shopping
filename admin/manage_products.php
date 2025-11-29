<?php
session_start();
include "../includes/db_connect.php";
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php"); exit();
}

// Handle Add product
$msg = "";
if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $stock = intval($_POST['stock'] ?? 0);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $q = "INSERT INTO products (name, price, image, description, category, stock) VALUES ('$name', $price, '$image', '$desc', '$category', $stock)";
    if (mysqli_query($conn, $q)) {
        $msg = "Product added successfully!";
    } else {
        $msg = "Error: ".mysqli_error($conn);
    }
}

// Handle Edit product
if (isset($_GET['edit'])) {
    $pid = intval($_GET['edit']);
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE product_id = $pid"));
}

// Update product details
if (isset($_POST['update'])) {
    $pid = intval($_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $stock = intval($_POST['stock'] ?? 0);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $q = "UPDATE products SET name='$name', price=$price, image='$image', description='$desc', category='$category', stock=$stock WHERE product_id=$pid";
    if (mysqli_query($conn, $q)) {
        $msg = "Product updated successfully!";
        header("Location: manage_products.php");
        exit();
    } else {
        $msg = "Error: ".mysqli_error($conn);
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE product_id = $pid");
}

$products = mysqli_query($conn, "SELECT * FROM products");
$admin_name = $_SESSION['admin_name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
        :root {
            --side-bg: #ff4e64;
            --side-hover: #d32f2f;
            --main-bg: #fff;
            --box-bg: #fff;
            --accent: #ff4e64;
            --accent-dark: #d32f2f;
            --text: #181518;
            --danger: #e74c3c;
            --success: #27ae60;
        }
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: var(--main-bg);}
        .admin-container { display: flex; }
        .sidebar { width: 230px; background: var(--side-bg); min-height: 100vh; color: #fff; position: sticky; top: 0; display: flex; flex-direction: column; align-items: stretch;}
        .sidebar h2 { font-size: 2rem; text-align: center; margin: 28px 0 26px 0; font-weight: 700; letter-spacing: .03em;}
        .side-nav { flex: 1; display: flex; flex-direction: column; gap: 12px;}
        .side-nav a { color: #fff; text-decoration: none; padding: 13px 35px; font-size: 1.08rem; font-weight: 600; display: flex; align-items: center; gap: 15px; border-left: 4px solid transparent; transition: background .14s, border .18s, color .18s;}
        .side-nav a:hover, .side-nav a.active { background: var(--side-hover); border-left: 4px solid #fff; color: #fff; }
        .side-bottom { margin-top: auto; padding-bottom: 20px;}
        .logout-btn, .side-bottom a { display: flex; align-items: center; color: #fff; padding: 13px 35px; font-size: 1.08rem; font-weight: 600; border-left: 4px solid transparent; background: none; border: none; width: 100%; text-align: left; cursor: pointer; transition: background .14s, border .18s, color .18s;}
        .logout-btn:hover, .side-bottom a:hover { background: var(--side-hover); color: #fff; border-left: 4px solid #fff;}
        .main { flex: 1; background: var(--main-bg); min-height: 100vh; padding-bottom: 36px; }
        .topbar { background: #fff; box-shadow: 0 2px 16px #ff4e6465; display: flex; justify-content: flex-end; align-items: center; padding: 18px 38px; font-weight: 600; font-size: 1.12rem;}
        .admin-profile { display: flex; align-items: center; gap: 12px;}
        .admin-profile img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border:2px solid var(--accent);}
        .main-content { padding: 32px;}
        .product-card-form { background: var(--box-bg); border-radius: 13px; box-shadow: 0 4px 22px #ff4e6422; padding: 28px 22px 22px 22px; max-width: 540px; margin: 0 auto 36px auto;}
        .product-card-form input[type="text"], .product-card-form input[type="number"], .product-card-form input[type="url"], .product-card-form textarea { width: 100%; padding: 10px; margin: 11px 0 18px 0; border-radius: 6px; border: 1px solid #ffdee5; font-size: 1rem; background: #fff6f7; }
        .product-card-form label { font-weight: 600; color: var(--accent); }
        .product-card-form button { background: var(--accent); color: #fff; border: none; padding: 12px 28px; border-radius: 33px; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-top: 7px; transition: background 0.19s; }
        .product-card-form button:hover { background: var(--side-hover); }
        .success-message { color: var(--success); margin-bottom: 15px; font-weight: 600; text-align: center; }
        .err-message { color: var(--danger); margin-bottom: 15px; font-weight: 600; text-align: center; }
        .prod-table-cont { background: #fff; border-radius: 13px; padding: 18px; box-shadow: 0 4px 22px #ff4e6422; margin:0 auto; max-width:1240px;}
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 9px; overflow: hidden; margin-top: 10px;}
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #ffdde8;}
        th { background: var(--accent); color: #fff; font-weight:600; font-size: 1rem;}
        tr:last-child td { border-bottom: none; }
        img { width: 60px; border-radius: 5px;}
        .prod-actions a { display: inline-block; padding: 7px 14px; border-radius: 25px; color: #fff; font-weight: 600; text-decoration: none; margin: 0 3px; font-size: 1rem;}
        .prod-actions .del { background: var(--danger);}
        .prod-actions .del:hover { background: #a62c1a;}
        .prod-actions .edit { background: var(--accent);}
        .prod-actions .edit:hover { background: var(--side-hover);}
        @media (max-width: 900px) { .admin-container { flex-direction: column; } .main-content { padding: 10px;} .prod-table-cont { padding:4px;} .product-card-form{padding:15px;} table{font-size:.92rem;} th,td{padding:.6rem;} }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <h2>Admin</h2>
        <nav class="side-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a class="active" href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
            <a href="orderlist.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        </nav>
        <div class="side-bottom">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>
    <main class="main">
        <div class="topbar">
            <div class="admin-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_name); ?>" alt="Profile" />
                <?php echo htmlspecialchars($admin_name); ?> &bull; <span style="color:#ff4e64;font-size:.91em;">Online</span>
            </div>
        </div>
        <div class="main-content">
            <div class="product-card-form">
                <h3 style="text-align:center; margin-top:0;">
                    <?php echo isset($product) ? 'Edit Product' : 'Add New Product'; ?>
                </h3>
                <?php if ($msg): ?>
                    <div class="<?php echo (strpos($msg,'successfully')!==false)?"success-message":"err-message"; ?>">
                        <?= htmlspecialchars($msg); ?>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <?php if (isset($product)): ?>
                        <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                    <?php endif; ?>
                    <label>Product Name</label>
                    <input type="text" name="name" placeholder="Product Name" value="<?= isset($product) ? htmlspecialchars($product['name']) : ''; ?>" required>
                    <label>Category</label>
                    <input type="text" name="category" placeholder="Category (groceries/electronics)" value="<?= isset($product) ? htmlspecialchars($product['category']) : ''; ?>" required>
                    <label>Price</label>
                    <input type="number" name="price" placeholder="Price" step="0.01" value="<?= isset($product) ? htmlspecialchars($product['price']) : ''; ?>" required>
                    <label>Image URL</label>
                    <input type="url" name="image" placeholder="Image URL" value="<?= isset($product) ? htmlspecialchars($product['image']) : ''; ?>" required>
                    <label>Stock (quantity)</label>
                    <input type="number" name="stock" placeholder="Stock" value="<?= isset($product) ? htmlspecialchars($product['stock'] ?? 0) : '0'; ?>" min="0" required>
                    <label>Description</label>
                    <textarea name="description" placeholder="Description" required><?= isset($product) ? htmlspecialchars($product['description']) : ''; ?></textarea>
                    <div style="text-align:center;">
                    <?php if (isset($product)): ?>
                        <button name="update">Update Product</button>
                    <?php else: ?>
                        <button name="add">Add Product</button>
                    <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="prod-table-cont">
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Image</th>
                        <th>Delete / Edit</th>
                    </tr>
                    <?php while($p = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category'] ?? '') ?></td>
                        <td>&#8377;<?= htmlspecialchars(number_format($p['price'], 2)) ?></td>
                        <td><?= intval($p['stock'] ?? 0) ?></td>
                        <td><img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"></td>
                        <td class="prod-actions">
                            <a href="manage_products.php?delete=<?= $p['product_id'] ?>" class="del" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                            <a href="manage_products.php?edit=<?= $p['product_id'] ?>" class="edit"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
