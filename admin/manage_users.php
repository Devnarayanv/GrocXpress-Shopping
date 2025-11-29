<?php
session_start();
include "../includes/db_connect.php";
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    if ($uid != $_SESSION['user_id']) { // Prevent deleting self
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $uid AND is_admin = 0");
    }
}

$users = mysqli_query($conn, "SELECT * FROM users");
$admin_name = $_SESSION['admin_name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
        }
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: var(--main-bg);}
        .admin-container { display: flex; }
        .sidebar {
            width: 230px;
            background: var(--side-bg);
            min-height: 100vh; color: #fff;
            position: sticky; top: 0;
            display: flex; flex-direction: column; align-items: stretch;
        }
        .sidebar h2 { font-size: 2rem; text-align: center; margin: 28px 0 26px 0; font-weight: 700; letter-spacing:.03em;}
        .side-nav { flex: 1; display: flex; flex-direction: column; gap: 12px;}
        .side-nav a { color: #fff; text-decoration: none; padding: 13px 35px; font-size: 1.08rem; font-weight: 600; display: flex; align-items: center; gap: 15px; border-left: 4px solid transparent; transition: background .14s, border .18s, color .18s;}
        .side-nav a:hover, .side-nav a.active { background: var(--side-hover); border-left: 4px solid #fff; color: #fff; }
        .side-bottom { margin-top: auto; padding-bottom: 20px;}
        .logout-btn, .side-bottom a { display: flex; align-items: center; color: #fff; padding: 13px 35px; font-size: 1.08rem; font-weight: 600; border-left: 4px solid transparent; background: none; border: none; width: 100%; text-align: left; cursor: pointer; transition: background .14s, border .18s, color .18s;}
        .logout-btn:hover, .side-bottom a:hover { background: var(--side-hover); color: #fff; border-left: 4px solid #fff;}
        .main { flex: 1; background: var(--main-bg); min-height: 100vh; padding-bottom: 36px; }
        .topbar {
            background: #fff;
            box-shadow: 0 2px 16px #ff4e6465;
            display: flex; justify-content: flex-end; align-items: center;
            padding: 18px 38px; font-weight: 600; font-size: 1.12rem;
        }
        .admin-profile { display: flex; align-items: center; gap: 12px;}
        .admin-profile img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border:2px solid var(--accent);}
        .main-content { padding: 32px; }
        .user-table-cont {
            background: #fff; border-radius: 13px; padding: 18px; box-shadow: 0 4px 22px #ff4e6422; margin:0 auto; max-width:900px;
        }
        .main-content h2 { margin:0 0 18px 0; color: var(--accent); text-align: center;}
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 9px; overflow: hidden; margin-top: 10px;}
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #ffdde8;}
        th { background: var(--accent); color: #fff; font-weight:600; font-size: 1rem;}
        tr:last-child td { border-bottom: none; }
        .del-btn { color: #fff; background: var(--danger); border: none; border-radius: 18px; font-size: 1.05rem; font-weight: 600; padding: 6px 16px; text-decoration: none; cursor: pointer; display:inline-block;}
        .del-btn:hover { background: #a62c1a;}
        @media (max-width: 900px) {
            .admin-container { flex-direction: column; }
            .main-content { padding: 10px;}
            .user-table-cont { padding:4px;}
            table{font-size:.92rem;}
            th,td{padding:.6rem;}
        }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <h2>Admin</h2>
        <nav class="side-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a class="active" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
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
            <div class="user-table-cont">
                <h2>Manage Users</h2>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Admin?</th>
                        <th>Delete</th>
                    </tr>
                    <?php while($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['is_admin'] ? "Yes" : "No"; ?></td>
                        <td>
                            <?php
                            if ($u['is_admin']) {
                                echo "-";
                            } else {
                                echo '<a href="manage_users.php?delete='.$u['user_id'].'" class="del-btn" onclick="return confirm(\'Delete this user?\')"><i class="fas fa-trash"></i> Delete</a>';
                            }
                            ?>
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
