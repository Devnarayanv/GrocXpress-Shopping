<?php
session_start();
include "includes/db_connect.php";

$order_id = intval($_GET['id'] ?? 0);

// Determine if current viewer is admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// If not logged in as either a user or admin, redirect to login
if (!isset($_SESSION['user_id']) && !$is_admin) {
    header("Location: login.php"); exit();
}

// If admin, fetch order by id only; otherwise ensure the order belongs to the logged-in user
if ($is_admin) {
    $orderq = mysqli_query($conn, "SELECT * FROM orders WHERE order_id=$order_id");
} else {
    $user_id = $_SESSION['user_id'];
    $orderq = mysqli_query($conn, "SELECT * FROM orders WHERE order_id=$order_id AND user_id=$user_id");
}
$order = mysqli_fetch_assoc($orderq);
if (!$order) { echo "<h2 style='color:#d32f2f;'>Order Not Found.</h2>"; exit(); }

// Fetch order items and join with product details
$itemsq = mysqli_query($conn,
    "SELECT order_items.*, products.name, products.image 
     FROM order_items 
     JOIN products ON order_items.product_id=products.product_id 
     WHERE order_items.order_id=$order_id"
);

// Fetch user details (name, address, email, phone) for packaging sticker
$user = null;
if (!empty($order['user_id'])) {
    $userq = mysqli_query($conn, "SELECT name, email, phone, address, city, state, pin FROM users WHERE user_id=".intval($order['user_id']));
    if ($userq) $user = mysqli_fetch_assoc($userq);
}

// If download requested, generate a PNG sticker image (fallback to plain text if GD not available)
if (isset($_GET['download']) && $_GET['download']) {
    // build lines from user data (header info) - we'll also fetch items below
    $lines = [];
    $lines[] = "Order #: " . $order_id;
    if (!empty($user['name'])) $lines[] = "Name: " . $user['name'];

    // Full address as multiple lines
    $addrParts = [];
    if (!empty($user['address'])) $addrParts[] = $user['address'];
    $cityLine = [];
    if (!empty($user['city'])) $cityLine[] = $user['city'];
    if (!empty($user['state'])) $cityLine[] = $user['state'];
    if (!empty($user['pin'])) $cityLine[] = $user['pin'];
    if (!empty($cityLine)) $addrParts[] = implode(', ', $cityLine);
    if (!empty($addrParts)) {
        $lines[] = "";
        foreach ($addrParts as $a) $lines[] = $a;
    }
    if (!empty($user['email'])) $lines[] = "";
    if (!empty($user['email'])) $lines[] = "Email: " . $user['email'];
    if (!empty($user['phone'])) $lines[] = "Phone: " . $user['phone'];

    // Fetch order items to include product details in the sticker
    $items = [];
    $items_total = 0.0;
    $itemsq_local = mysqli_query($conn,
        "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id=p.product_id WHERE oi.order_id=".intval($order_id)
    );
    if ($itemsq_local) {
        while ($it = mysqli_fetch_assoc($itemsq_local)) {
            $qty = (float)$it['quantity'];
            $unit = (float)$it['unit_price'];
            $subtotal = $qty * $unit;
            $items_total += $subtotal;
            $items[] = [
                'name' => $it['name'] ?? 'Item',
                'qty' => $qty,
                'unit' => $unit,
                'subtotal' => $subtotal
            ];
        }
    }

    // Try to generate PNG with GD
    if (function_exists('imagecreatetruecolor')) {
        // Try to locate a TTF font on system
        $possibleFonts = [
            __DIR__ . '/../assets/fonts/Roboto-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            'C:/Windows/Fonts/arial.ttf'
        ];
        $font = null;
        foreach ($possibleFonts as $p) {
            if (file_exists($p)) { $font = $p; break; }
        }

    // larger sticker for printing: wider and taller
    $width = 1000;
    $lineHeight = $font ? 34 : 16; // larger line height when TTF available
    $padding = 30;
    $barcodeArea = 160; // reserve space at bottom for barcode and human text

    // Estimate additional height for items table (each item will take ~lineHeight)
    $itemsLines = max(0, count($items));
    // Add header row + each item + one total row
    $itemsBlockLines = $itemsLines > 0 ? (1 + $itemsLines + 1) : 0;

    $height = $padding * 2 + (count($lines) + $itemsBlockLines) * $lineHeight + $barcodeArea;

        $img = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($img, 255,255,255);
        $black = imagecolorallocate($img, 20,20,20);
        $gray = imagecolorallocate($img, 90,90,90);
        imagefilledrectangle($img, 0,0,$width,$height,$white);

        // Draw a thin border
        $border = imagecolorallocate($img, 200,200,200);
        imagerectangle($img, 0,0,$width-1,$height-1,$border);

        $y = $padding + 6;
        if ($font) {
            // Use TTF with better sizing
            $titleSize = 28;
            imagettftext($img, $titleSize, 0, $padding, $y + $titleSize, $black, $font, trim($lines[0]));
            $y += $lineHeight;
            // rest of lines
            for ($i = 1; $i < count($lines); $i++) {
                $text = $lines[$i];
                imagettftext($img, 18, 0, $padding, $y + 18, $black, $font, $text);
                $y += $lineHeight;
            }

            // Leave a small gap before items
            $y += 6;

            // Render items table header if items present
            if (count($items) > 0) {
                imagettftext($img, 18, 0, $padding, $y + 18, $black, $font, 'Items:');
                $y += $lineHeight;

                // Column headers
                $colX_name = $padding;
                $colX_qty = $padding + 520;
                $colX_unit = $padding + 640;
                $colX_sub = $padding + 820;
                imagettftext($img, 16, 0, $colX_name, $y + 16, $black, $font, 'Product');
                imagettftext($img, 16, 0, $colX_qty, $y + 16, $black, $font, 'Qty');
                imagettftext($img, 16, 0, $colX_unit, $y + 16, $black, $font, 'Unit');
                imagettftext($img, 16, 0, $colX_sub, $y + 16, $black, $font, 'Subtotal');
                $y += $lineHeight;

                foreach ($items as $it) {
                    $name = $it['name'];
                    // truncate name if too long
                    if (mb_strlen($name) > 40) $name = mb_substr($name,0,37).'...';
                    imagettftext($img, 16, 0, $colX_name, $y + 16, $black, $font, $name);
                    imagettftext($img, 16, 0, $colX_qty, $y + 16, $black, $font, (int)$it['qty']);
                    imagettftext($img, 16, 0, $colX_unit, $y + 16, $black, $font, number_format($it['unit'], 2));
                    imagettftext($img, 16, 0, $colX_sub, $y + 16, $black, $font, number_format($it['subtotal'], 2));
                    $y += $lineHeight;
                }

                // Draw totals
                $y += 4;
                imagettftext($img, 18, 0, $colX_sub - 120, $y + 18, $black, $font, 'Items Total:');
                imagettftext($img, 18, 0, $colX_sub, $y + 18, $black, $font, number_format($items_total, 2));
                $y += $lineHeight;
            }
        } else {
            // Fallback to built-in font rendering
            imagestring($img, 5, $padding, $y, $lines[0], $black);
            $y += $lineHeight;
            for ($i = 1; $i < count($lines); $i++) {
                imagestring($img, 3, $padding, $y, $lines[$i], $gray);
                $y += $lineHeight;
            }

            // Built-in font items rendering (simple)
            if (count($items) > 0) {
                $y += 6;
                imagestring($img, 4, $padding, $y, 'Items:', $black);
                $y += $lineHeight;
                foreach ($items as $it) {
                    $line = sprintf("%s x%d @ %.2f = %.2f", $it['name'], (int)$it['qty'], $it['unit'], $it['subtotal']);
                    imagestring($img, 3, $padding, $y, $line, $black);
                    $y += $lineHeight;
                }
                $y += 4;
                imagestring($img, 4, $padding, $y, 'Items Total: ' . number_format($items_total,2), $black);
                $y += $lineHeight;
            }
        }

        // ====== Code128 barcode generation (simple implementation) ======
        // We'll encode the order id as Code128 subset B and draw bars below the text
        $code = (string)$order_id;
        // Code128 patterns for values 0-106 (from specification)
        $patterns = [
            "11011001100","11001101100","11001100110","10010011000","10010001100",
            "10001001100","10011001000","10011000100","10001100100","11001001000",
            "11001000100","11000100100","10110011100","10011011100","10011001110",
            "10111001100","10011101100","10011100110","11001110010","11001011100",
            "11001001110","11011100100","11001110100","11101101110","11101001100",
            "11100101100","11100100110","11101100100","11100110100","11100110010",
            "11011011000","11011000110","11000110110","10100011000","10001011000",
            "10001000110","10110001000","10001101000","10001100010","11010001000",
            "11000101000","11000100010","10110111000","10110001110","10001101110",
            "10111011000","10111000110","10001110110","11101110110","11010001110",
            "11000101110","11011101000","11011100010","11011101110","11101011000",
            "11101000110","11100010110","11101101000","11101100010","11100011010",
            "11101111010","11001000010","11110001010","10100110000","10100001100",
            "10010110000","10010000110","10000101100","10000100110","10110010000",
            "10110000100","10011010000","10011000010","10000110100","10000110010",
            "11000010010","11001010000","11110111010","11000010100","10001111010",
            "10100111100","10010111100","10010011110","10111100100","10011110100",
            "10011110010","11110100100","11110010100","11110010010","11011011110",
            "11011110110","11110110110","10101111000","10100011110","10001011110",
            "10111101000","10111100010","11110101000","11110100010","10111011110",
            "10111101110","11101011110","10111110110","11110101110","11010000100",
            "11010010000","11010011100","11000111010"
        ];

        // Map characters to Code128 B values
        $strVals = array();
        for ($i = 0; $i < strlen($code); $i++) {
            $ch = ord($code[$i]);
            if ($ch >= 32 && $ch <= 127) {
                $strVals[] = $ch - 32;
            } else {
                // unsupported char: use ?
                $strVals[] = ord('?') - 32;
            }
        }

        // Build full sequence: start code B (104), data, checksum, stop (106)
        $start = 104;
        $sum = $start;
        $seq = array($start);
        for ($i = 0; $i < count($strVals); $i++) {
            $seq[] = $strVals[$i];
            $sum += $strVals[$i] * ($i+1);
        }
        $check = $sum % 103;
        $seq[] = $check;
        $seq[] = 106; // stop

        // Convert sequence to a wide/narrow bar string
        $barString = '';
        foreach ($seq as $val) {
            if (!isset($patterns[$val])) continue;
            $barString .= $patterns[$val];
        }

        // Draw barcode starting at bottom center
        $barcodeX = $padding;
        $barcodeY = $height - $barcodeArea + 10;
        $barcodeWidth = $width - $padding * 2;

        // Compute total modules and scale to fit width
        $totalModules = strlen($barString);
        $moduleWidth = max(1, floor($barcodeWidth / $totalModules));
        $currentX = $barcodeX;
        for ($i = 0; $i < $totalModules; $i++) {
            $color = ($barString[$i] === '1') ? $black : $white;
            if ($moduleWidth >= 1) {
                imagefilledrectangle($img, $currentX, $barcodeY, $currentX + $moduleWidth - 1, $barcodeY + 70, $color);
            }
            $currentX += $moduleWidth;
        }

        // Human-readable order id under barcode
        $hrY = $barcodeY + 70 + 26;
        if ($font) {
            imagettftext($img, 18, 0, $padding, $hrY, $black, $font, 'Order ID: ' . $code);
        } else {
            imagestring($img, 3, $padding, $hrY, 'Order ID: ' . $code, $black);
        }

        // Output PNG
        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="sticker_order_'. $order_id .'.png"');
        header('Content-Length: ' . strlen($pngData));
        echo $pngData;
        exit();
    }

    // If GD not available, fall back to plain text download
    $content = implode("\n", $lines) . "\n";
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sticker_order_'. $order_id .'.txt"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order #<?=$order_id?> - GrocXpress</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
       body { font-family:'Segoe UI', Arial,sans-serif; background:#eef2ff; color:#2d3748; }
.wrap { max-width:650px; margin:36px auto; background:#fff; border-radius:16px; box-shadow:0 8px 32px #667eea22; padding:42px 3vw 2vw 3vw;}
h2 {color: #667eea;text-align:center;margin-bottom:1.4em;}
.topinfo {margin-bottom:29px; border-radius:10px; background:#eaf0ff; padding:18px 16px;}
.topinfo b {display:inline-block;width:140px;}
.status.paid {color:#219150;font-weight:700;}
.status.unpaid,.status.cancelled {color:#5a67d8;font-weight:700;}
.items-table {width:100%; border-collapse:collapse;}
.items-table th,.items-table td {padding:13px 8px;text-align:left;font-size:1.03em;border-bottom:1px solid #c3d0f5;}
.items-table th {background:#667eea;color:#fff;font-weight:700;}
.items-table img {width:48px;height:48px;object-fit:cover;border-radius:8px;}
.ttotal {text-align:right;font-size:1.27em; color:#667eea;font-weight:800;padding:15px 0 4px 0;}
.back-link {display:inline-block; margin:2.2em auto 0 auto; color:#667eea; font-weight:700; border:1px solid #667eea; border-radius:30px; padding:8px 25px; text-decoration:none;}
.back-link:hover {background:#667eea; color:#fff;}
@media (max-width:600px){
    .wrap{padding:14px 1vw;}
    .topinfo b{width:88px;}
    .items-table th,.items-table td{font-size:.95em;}
}

    </style>
</head>
<body>
<div class="wrap">
    <h2><i class="fas fa-file-invoice"></i> Order #<?=$order_id?></h2>
    <div class="topinfo">
        <div><b>Order Date:</b> <?=htmlspecialchars(date('d M Y, h:i A', strtotime($order['order_date'])))?></div>
        <div><b>Status:</b> <span class="status <?=strtolower($order['status'])?>"><?=htmlspecialchars($order['status'])?></span></div>
        <div><b>Payment:</b>
            <?php if ($order['razorpay_payment_id']) { ?>
                <span style="color:#219150"><i class="fas fa-check-circle"></i> Paid</span>
                <span style="color:#555;font-size:.96em;">(ID: <?=htmlspecialchars($order['razorpay_payment_id'])?>)</span>
            <?php } else { ?>
                <span style="color:#d32f2f;"><i class="fas fa-times-circle"></i> Not Paid</span>
            <?php } ?>
        </div>
        <div><b>Total:</b> ₹<?=number_format($order['total_amount'],2)?></div>
    </div>
    <?php if ($user): ?>
        <div style="margin:12px 0 18px 0;padding:14px;border-radius:10px;background:#f7f9ff;border:1px solid #e1e8ff;">
            <h4 style="margin:0 0 8px 0;color:#334155;">Shipping Address</h4>
            <div style="font-weight:700;"><?=htmlspecialchars($user['name'] ?? '')?></div>
            <div><?=nl2br(htmlspecialchars($user['address'] ?? ''))?></div>
            <div><?=htmlspecialchars(trim(($user['city'] ?? '') . ', ' . ($user['state'] ?? '') . ' ' . ($user['pin'] ?? ''))) ?></div>
            <div style="margin-top:6px;color:#475569;">Email: <?=htmlspecialchars($user['email'] ?? '')?> | Phone: <?=htmlspecialchars($user['phone'] ?? '')?></div>
            <div style="margin-top:10px;"><a class="btn" href="order_detail.php?id=<?=$order_id?>&download=1">Download Sticker</a></div>
        </div>
    <?php endif; ?>
    <h3 style="color:#d32f2f;margin:16px 0 7px 0;">Items:</h3>
    <table class="items-table">
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
        <?php $gtotal = 0; while($item = mysqli_fetch_assoc($itemsq)): 
            $subtotal = $item['quantity'] * $item['price'];
            $gtotal += $subtotal;
        ?>
        <tr>
            <td><img src="<?=htmlspecialchars($item['image'])?>" alt="<?=htmlspecialchars($item['name'])?>"></td>
            <td><?=htmlspecialchars($item['name'])?></td>
            <td><?=htmlspecialchars($item['quantity'])?></td>
            <td>₹<?=number_format($item['price'],2)?></td>
            <td>₹<?=number_format($subtotal,2)?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <div class="ttotal">Grand Total: ₹<?=number_format($gtotal,2)?></div>
    <a href="orders.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Orders</a>
</div>
</body>
</html>
