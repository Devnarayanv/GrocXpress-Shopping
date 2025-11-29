<?php
session_start();
include "../includes/db_connect.php";

// Admin auth
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php"); exit();
}

// Actions: mark shipped / cancel
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    if ($_GET['action'] === 'mark_shipped') {
        mysqli_query($conn, "UPDATE orders SET status='Shipped' WHERE order_id=$order_id");
    } elseif ($_GET['action'] === 'mark_cancelled') {
        mysqli_query($conn, "UPDATE orders SET status='Cancelled' WHERE order_id=$order_id");
    }
    header("Location: orderlist.php"); exit();
}

// Filters
$where = "1=1";
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';
if ($from) { $f = mysqli_real_escape_string($conn, $from); $where .= " AND DATE(o.order_date) >= '$f'"; }
if ($to)   { $t = mysqli_real_escape_string($conn, $to); $where .= " AND DATE(o.order_date) <= '$t'"; }
if ($status) { $s = mysqli_real_escape_string($conn, $status); $where .= " AND o.status='$s'"; }

$rev_q = mysqli_query($conn, "SELECT COALESCE(SUM(o.total_amount),0) as revenue, COUNT(*) as cnt FROM orders o WHERE ($where) AND (LOWER(o.status) IN ('paid','shipped') OR o.razorpay_payment_id IS NOT NULL)");
$rev_row = mysqli_fetch_assoc($rev_q);
$revenue = number_format((float)$rev_row['revenue'], 2);
$count = intval($rev_row['cnt']);

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $csv_q = mysqli_query($conn, "SELECT o.*, u.name AS user_name, u.email AS user_email FROM orders o LEFT JOIN users u ON o.user_id=u.user_id WHERE ($where) ORDER BY o.order_date DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=orders_export_'.date('Ymd_His').'.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['order_id','order_date','user_name','user_email','total_amount','status','razorpay_payment_id','razorpay_order_id']);
    while($r = mysqli_fetch_assoc($csv_q)) {
        fputcsv($out, [$r['order_id'],$r['order_date'],$r['user_name'],$r['user_email'],$r['total_amount'],$r['status'],$r['razorpay_payment_id'],$r['razorpay_order_id']]);
    }
    fclose($out); exit();
}

// Fetch orders
$q = "SELECT o.*, u.name AS user_name, u.email AS user_email FROM orders o LEFT JOIN users u ON o.user_id=u.user_id WHERE ($where) ORDER BY o.order_date DESC LIMIT 1000";
$res = mysqli_query($conn, $q);
$db_error = '';
if ($res === false) {
    $db_error = 'Database error: ' . htmlspecialchars(mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - Orders</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
        body{font-family:Arial, sans-serif;background:#f7f8fb;padding:18px}
        .panel{background:#fff;padding:18px;border-radius:12px;box-shadow:0 6px 20px #00000010}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
        th{background:#f2f6ff;color:#224}
        .meta{display:flex;gap:12px;align-items:center}
        .meta .stat{background:#fff6f7;padding:8px 12px;border-radius:8px;border:1px solid #ffe6ea}
        .btn{background:#ff4e64;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:700}
        .btn.secondary{background:#666}
        .filters{margin-top:12px;display:flex;gap:8px;align-items:center}
        .filters input, .filters select{padding:8px;border-radius:6px;border:1px solid #ddd}
    </style>
</head>
<body>
    <div class="panel">
        <h2>Orders</h2>
        <div class="meta">
            <div class="stat"><strong>Revenue:</strong> ₹<?php echo $revenue; ?></div>
            <div class="stat"><strong>Orders:</strong> <?php echo $count; ?></div>
            <div style="margin-left:auto">
                <a class="btn" href="orderlist.php?export=csv&<?php echo http_build_query($_GET); ?>">Export CSV</a>
                <a class="btn secondary" href="orderlist.php">Reset</a>
            </div>
        </div>

        <?php if ($db_error): ?>
            <div style="margin-top:12px;padding:12px;border-radius:8px;background:#ffe9e9;color:#a00;border:1px solid #f5c2c2;">
                <strong>Error:</strong> <?php echo $db_error; ?>
            </div>
        <?php endif; ?>

        <form class="filters" method="get">
            <label>From <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>"></label>
            <label>To <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>"></label>
            <label>Status
                <select name="status">
                    <option value="">All</option>
                    <option value="Paid" <?php if($status==='Paid') echo 'selected'; ?>>Paid</option>
                    <option value="Unpaid" <?php if($status==='Unpaid') echo 'selected'; ?>>Unpaid</option>
                    <option value="Shipped" <?php if($status==='Shipped') echo 'selected'; ?>>Shipped</option>
                    <option value="Cancelled" <?php if($status==='Cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </label>
            <button class="btn" type="submit">Apply</button>
        </form>

        <table>
            <tr><th>#</th><th>Date</th><th>User</th><th>Amount (₹)</th><th>Status</th><th>Payment</th><th>Actions</th></tr>
            <?php while($o = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo intval($o['order_id']); ?></td>
                <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($o['order_date']))); ?></td>
                <td><?php echo htmlspecialchars($o['user_name'] ?? 'Guest') . (isset($o['user_email']) ? ' <'.htmlspecialchars($o['user_email']).'>' : ''); ?></td>
                <td><?php echo number_format((float)$o['total_amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($o['status']); ?></td>
                <td><?php echo htmlspecialchars($o['razorpay_payment_id'] ?: $o['razorpay_order_id'] ?: '—'); ?></td>
                <td>
                    <a class="btn secondary" href="../order_detail.php?id=<?php echo intval($o['order_id']); ?>">View</a>
                    <a class="btn" href="../order_detail.php?id=<?php echo intval($o['order_id']); ?>&download=1">Download Sticker</a>
                    <?php if(strtolower($o['status'])!=='shipped'): ?>
                        <a class="btn" href="orderlist.php?action=mark_shipped&order_id=<?php echo intval($o['order_id']); ?>" onclick="return confirm('Mark order #<?php echo intval($o['order_id']); ?> as shipped?')">Mark Shipped</a>
                    <?php endif; ?>
                    <?php if(strtolower($o['status'])!=='cancelled'): ?>
                        <a class="btn" style="background:#777" href="orderlist.php?action=mark_cancelled&order_id=<?php echo intval($o['order_id']); ?>" onclick="return confirm('Cancel order #<?php echo intval($o['order_id']); ?>?')">Cancel</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
