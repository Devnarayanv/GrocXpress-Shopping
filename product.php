<?php
session_start();
include "includes/db_connect.php";
$id = intval($_GET['id'] ?? 0);
$q = mysqli_query($conn, "SELECT * FROM products WHERE product_id=$id LIMIT 1");
$product = mysqli_fetch_assoc($q);
if(!$product) { echo "<h2>Product not found</h2>"; exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=htmlspecialchars($product['name'])?> - GrocXpress</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <style>
      :root {
    --blue: #667eea;
    --blue-dark: #5a67d8;
    --box: #fff;
    --muted: #a0aec0;
    --border: #c3d0f5;
    --bg: #fff;
}
body { background:var(--bg); color:#2d3748; font-family:'Segoe UI',Arial,sans-serif; margin:0;}
.prod-wrap { max-width: 1150px; margin: 40px auto; display: flex; gap: 2.5vw; background: var(--box); border-radius: 18px; box-shadow:0 2px 32px #667eea22; padding:2.5rem 2rem 2.5rem 2rem; flex-wrap:wrap;}
.img-col { flex:1 1 230px; min-width:230px; max-width: 350px;}
.img-main { width: 100%; border-radius: 14px; box-shadow:0 1.5px 9px #667eea33; margin-bottom:13px;}
.thumbs { display:flex; gap:7px; margin-bottom:13px;}
.thumbs img { width:50px;height:50px;object-fit:cover;border-radius:7px;box-shadow:0 1px 5px #c3d0f5;cursor:pointer;border:2px solid #fff;transition:border .13s;}
.thumbs img.active, .thumbs img:hover {border:2px solid var(--blue);}
.prod-details { flex:2 1 350px; padding-left: 2vw;}
.prod-title { font-size:2rem;font-weight:800;color:var(--blue-dark);}
.prod-meta { margin:15px 0 24px 0; color:#555; font-size:1.1rem;}
.prod-price-row {display:flex;align-items:center;gap:20px; margin-bottom:9px;}
.prod-price {font-size:2.2rem;color:var(--blue);font-weight:900;}
.prod-mrp {text-decoration:line-through;color:var(--muted);}
.prod-offer {color: #128444; font-size:1.1rem;font-weight:600;margin-left:12px;}
.prod-desc {font-size:1.1rem;line-height:1.7;margin:1.1em 0 1.4em 0;}
.offer-cards {display:flex; flex-wrap:wrap; gap:1rem;margin:1.2em 0;}
.offer-card {background:#eef2ff;color:var(--blue-dark);padding:10px 17px;border-radius:12px;border:1px solid #c3d0f5;font-size:1em;}
.color-options {display:flex;gap:10px;margin:1.5em 0;}
.color-sw {width:44px;height:44px;border-radius:50%;border:2.5px solid #c3d0f5;box-shadow:0 1px 4px #eee;cursor:pointer;display:flex; align-items:center; justify-content:center;}
.color-sw.active, .color-sw:hover {border:2.5px solid var(--blue);}
.prod-actions {margin-top:1em;display:flex;gap:18px;}
.btn-buy, .btn-cart {
    background: var(--blue);
    color: #fff;
    border:none;
    border-radius: 33px;
    font-size:1.12rem; font-weight:800;
    padding: 14px 32px;
    cursor:pointer;transition:background .19s; box-shadow:0 2px 11px #c3d0f573;
}
.btn-buy:hover, .btn-cart:hover {background: var(--blue-dark);}
.add-wish {background: #eef2ff; color: var(--blue-dark); font-size:1.19rem; border-radius:50%; border:none; padding: 0.6em 0.8em; margin-left:7px; cursor:pointer; transition: background .15s;}
.add-wish:hover {background: var(--blue); color:#fff;}
.stock, .in-stock {color:#0a9746;font-weight:700; font-size:1rem;}
.out-stock {color:#c62828;font-weight:700;font-size:1rem;}
@media(max-width:950px){ .prod-wrap{flex-direction: column;gap:3vw;padding:1.2rem 0.8rem;}
    .prod-details{padding-left:0;}
}

    </style>
</head>
<body>
    <div class="prod-wrap">
        <div class="img-col">
            <img id="mainImg" src="<?=htmlspecialchars($product['image'])?>" class="img-main" alt="<?=htmlspecialchars($product['name'])?>">
            <div class="thumbs">
                <img src="<?=htmlspecialchars($product['image'])?>" alt="Main Image" onclick="switchImg(this)" class="active">
            </div>
        </div>
        <div class="prod-details">
            <div class="prod-title"><?=htmlspecialchars($product['name'])?></div>
            <div class="prod-meta">
                <?php if (isset($product['stock']) && intval($product['stock']) > 0): ?>
                    <span class="stock in-stock"><i class="fas fa-check-circle"></i> In Stock (<?= intval($product['stock']) ?> available)</span>
                <?php else: ?>
                    <span class="stock out-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                <?php endif; ?>
            </div>
            <div class="prod-price-row">
                <span class="prod-price">&#8377;<?=number_format($product['price'],2)?></span>
                <?php
                $mrp = isset($product['mrp']) ? floatval($product['mrp']) : 0;
                $price = floatval($product['price']);
                if ($mrp > 0 && $mrp > $price) {
                    $discount = round(100 - ($price / $mrp * 100));
                    echo '<span class="prod-mrp">&#8377;'.number_format($mrp,2).'</span>';
                    echo '<span class="prod-offer">-'.$discount.'% OFF</span>';
                }
                ?>
            </div>
            <div class="offer-cards">
                <div class="offer-card"><i class="fas fa-tag"></i> Cashback Upto ₹500 on Credit Cards</div>
                <div class="offer-card"><i class="fas fa-truck"></i> Free Delivery over ₹499</div>
                <div class="offer-card"><i class="fas fa-sync"></i> 7-day Easy Returns</div>
            </div>
            <?php if(!empty($product['color_options'])): // e.g. red,black,silver ?>
            <div><b>Available Colors:</b>
                <div class="color-options">
                   <?php foreach(explode(',',$product['color_options']) as $i=>$col): ?>
                      <span class="color-sw<?= $i===0?' active':'' ?>" style="background:<?=htmlspecialchars($col)?>" title="<?=htmlspecialchars(ucfirst($col))?>"></span>
                   <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="prod-desc"><?=nl2br(htmlspecialchars($product['description']))?></div>
            <div class="prod-actions">
                <?php if (isset($product['stock']) && intval($product['stock']) > 0): ?>
                    <a href="add_cart.php?id=<?=$product['product_id']?>" class="btn-cart"><i class="fas fa-shopping-cart"></i> Add to Cart</a>
                <?php else: ?>
                    <button class="btn-cart" disabled><i class="fas fa-shopping-cart"></i> Out of Stock</button>
                <?php endif; ?>
                
                <button class="add-wish" title="Add to Wishlist" onclick="location.href='add_wishlist.php?id=<?=$product['product_id']?>'"><i class="fas fa-heart"></i></button>
            </div>
        </div>
    </div>
    <script>
      // For switching main image if you have more thumbnails
      function switchImg(img){
          document.getElementById('mainImg').src = img.src;
          document.querySelectorAll('.thumbs img').forEach(t=>t.classList.remove('active'));
          img.classList.add('active');
      }
    </script>
</body>
</html>
