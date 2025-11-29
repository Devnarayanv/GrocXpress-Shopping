<?php
session_start();
include "../includes/db_connect.php";

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}
$admin_name = $_SESSION['admin_name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - GrocXpress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --muted: #ada9b1;
        }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: var(--main-bg);}
        .admin-container { display: flex; }
        .sidebar { width: 230px; background: var(--side-bg); min-height: 100vh; color: #fff; position: sticky; top: 0; display: flex; flex-direction: column; align-items: stretch;}
        .sidebar h2 { font-size: 2rem; text-align: center; margin: 28px 0 26px 0; font-weight: 700; letter-spacing: .03em;}
        .side-nav { flex: 1; display: flex; flex-direction: column; gap: 12px; }
        .side-nav a { color: #fff; text-decoration: none; padding: 13px 35px; font-size: 1.08rem; font-weight: 600; display: flex; align-items: center; gap: 15px; border-left: 4px solid transparent; transition: background .14s, border .18s, color .18s;}
        .side-nav a:hover, .side-nav a.active { background: var(--side-hover); border-left: 4px solid #fff; color: #fff; }
        .side-bottom { margin-top: auto; padding-bottom: 20px;}
        .logout-btn { display: flex; align-items: center; color: #fff; padding: 13px 35px; font-size: 1.08rem; font-weight: 600; border-left: 4px solid transparent; background: none; border: none; width: 100%; text-align: left; cursor: pointer; transition: background .14s, border .18s, color .18s; }
        .logout-btn:hover, .logout-btn:focus { background: var(--side-hover); color: #fff; border-left: 4px solid #fff;}
        .main { flex: 1; background: var(--main-bg); min-height: 100vh; padding-bottom: 36px; }
        .topbar { background: #fff; box-shadow: 0 2px 16px #ff4e6465; display: flex; justify-content: flex-end; align-items: center; padding: 18px 38px; font-weight: 600; font-size: 1.12rem;}
        .admin-profile { display: flex; align-items: center; gap: 12px;}
        .admin-profile img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent);}
        .main-content { padding: 32px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 32px; margin-bottom: 28px;}
        .stat-box { background: var(--box-bg); border-radius: 13px; padding: 28px 22px 22px 22px; box-shadow: 0 4px 22px #ff4e641f; display: flex; flex-direction: column; gap: 8px; align-items: flex-start;}
        .stat-box .stat-icon { font-size: 2rem; color: var(--accent); margin-bottom: 12px;}
        .stat-box .stat-label { font-weight: 600; font-size: 1rem; color: var(--muted);}
        .stat-box .stat-value { font-size: 1.7rem; font-weight: 700; color: var(--accent-dark);}
        .admin-actions { margin-top: 38px; display: flex; gap: 24px; flex-wrap: wrap;}
        .admin-btn { padding: 15px 38px; background: var(--accent); color: #fff; border: none; border-radius: 33px; font-size: 1.09rem; font-weight: 600; text-decoration: none; box-shadow: 0 2px 8px #ff4e643b; transition: background 0.19s, transform 0.13s;}
        .admin-btn:hover { background: var(--accent-dark); color: #fff; transform: scale(1.06);}
        @media (max-width: 900px) {
            .admin-container { flex-direction: column; }
            .sidebar { width: 100vw; flex-direction: row; min-height: 60px; padding: 0; }
            .side-nav { flex-direction: row; }
            .side-nav a { padding: 13px 14px; }
            .side-bottom { margin-top: 0; }
            .main-content { padding: 12px; }
            .dashboard-grid { grid-template-columns: 1fr; gap: 16px;}
        }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <h2>Admin</h2>
        <nav class="side-nav">
            <a class="active" href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
            <a href="orderlist.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="product_import.php"><i class="fas fa-shopping-cart"></i> IMPORT PRODUCTS CSV</a>
        </nav>
        <div class="side-bottom">
            <form method="post" action="../logout.php" style="margin:0;">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </aside>
    <main class="main">
        <div class="topbar">
            <div style="margin-right:auto;"></div>
            <button id="theme-toggle" title="Toggle theme" style="margin-right:12px; border-radius:8px; padding:8px 10px; background:transparent; border:1px solid rgba(0,0,0,0.06); cursor:pointer;"><i class="fas fa-moon"></i></button>
            <div class="admin-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_name); ?>" alt="Profile" />
                <?php echo htmlspecialchars($admin_name); ?> &bull; <span style="color:#ff4e64;font-size:.91em;">Online</span>
            </div>
        </div>
        <div class="main-content">
            <div class="dashboard-grid">
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-label">Users</div>
                    <div class="stat-value">
                        <?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE is_admin=0");
                        $row = mysqli_fetch_array($res); echo $row[0];
                        ?>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-box-open"></i></div>
                    <div class="stat-label">Products</div>
                    <div class="stat-value">
                        <?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) FROM products"); $row = mysqli_fetch_array($res); echo $row[0];
                        ?>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-label">Orders</div>
                    <div class="stat-value">
                        <?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) FROM orders"); $row = mysqli_fetch_array($res); echo $row[0];
                        ?>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
                    <div class="stat-label">Revenue</div>
                    <div class="stat-value">
                        <?php
                        $res = mysqli_query($conn, "SELECT SUM(total_amount) FROM orders"); $row = mysqli_fetch_array($res); echo isset($row[0]) ? '₹' . number_format($row[0]) : '₹0';
                        ?>
                    </div>
                </div>
            </div>
            <div class="admin-actions">
                <a href="manage_users.php" class="admin-btn"><i class="fas fa-users"></i> Manage Users</a>
                <a href="manage_products.php" class="admin-btn"><i class="fas fa-boxes"></i> Manage Products</a>
                <a href="orderlist.php" class="admin-btn"><i class="fas fa-shopping-cart"></i> Manage Orders</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
