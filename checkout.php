<?php
session_start();
include "includes/db_connect.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$err = $succ = "";

// Calculate cart total (update for your cart logic!)
$cart_total_q = mysqli_query($conn, "SELECT SUM(c.quantity * p.price) as total 
    FROM cart c JOIN products p ON c.product_id=p.product_id 
    WHERE c.user_id=$user_id");
$cart_total = mysqli_fetch_assoc($cart_total_q);
$pay_amount = $cart_total['total'] ?? 0;

// Handle add new address submission
if (isset($_POST['new_address'])) {
    $house_no = mysqli_real_escape_string($conn, $_POST['house_no']);
    $address_type = mysqli_real_escape_string($conn, $_POST['address_type']);
    $landmark = mysqli_real_escape_string($conn, $_POST['landmark']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $alt_mobile = mysqli_real_escape_string($conn, $_POST['alt_mobile']);

    $ins = "INSERT INTO addresses (user_id, house_no, address_type, landmark, city, state, pincode, mobile, alt_mobile) 
           VALUES ($user_id, '$house_no', '$address_type', '$landmark', '$city', '$state', '$pincode', '$mobile', '$alt_mobile')";
    if (mysqli_query($conn, $ins)) {
        $succ = "Address added successfully!";
    } else {
        $err = "Could not save address. Please try again.";
    }
}

// Load saved addresses
$addresses = mysqli_query($conn, "SELECT * FROM addresses WHERE user_id = $user_id ORDER BY address_id DESC");
$num_addresses = mysqli_num_rows($addresses);

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name,email FROM users WHERE user_id=$user_id"));

$keyId = "rzp_test_R6OB6DgSKiLo9B";
$keySecret = "sFC7fTRFmL2CTyL5JsWA9Q49";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - GrocXpress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            height: 100vh;
            color: #2d3748;
            line-height: 1.5;
            overflow: hidden;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 1.5rem;
            flex-shrink: 0;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.25rem;
        }
        
        .page-subtitle {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .checkout-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            flex: 1;
            min-height: 0;
        }
        
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        
        .section-title i {
            color: #667eea;
            font-size: 1rem;
        }
        
        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #d4f4dd;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Address List */
        .address-section {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        
        .address-list {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 1rem;
            padding-right: 0.5rem;
        }
        
        .address-list::-webkit-scrollbar {
            width: 4px;
        }
        
        .address-list::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }
        
        .address-list::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 2px;
        }
        
        .address-item {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }
        
        .address-item:hover {
            border-color: #667eea;
        }
        
        .address-item.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .address-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .address-radio {
            width: 20px;
            height: 20px;
            accent-color: #667eea;
            margin-top: 2px;
        }
        
        .address-details {
            flex: 1;
        }
        
        .address-type {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }
        
        .address-line {
            color: #4a5568;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .no-address-msg {
            text-align: center;
            padding: 1.5rem;
            color: #718096;
            font-style: italic;
        }
        
        /* Form Styles */
        .form-section {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        
        .form-container {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        
        .form-container::-webkit-scrollbar {
            width: 4px;
        }
        
        .form-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }
        
        .form-container::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 2px;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
            background: #fff;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .radio-group {
            display: flex;
            gap: 1rem;
            margin-top: 0.25rem;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            cursor: pointer;
        }
        
        .radio-option input {
            width: 16px;
            height: 16px;
            accent-color: #667eea;
        }
        
        .radio-option label {
            font-weight: 500;
            color: #4a5568;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        /* Buttons */
        .btn {
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
            width: 100%;
            flex-shrink: 0;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        /* Payment Box */
        .payment-card {
            background: #667eea;
            color: white;
            margin-top: 1rem;
            text-align: center;
            flex-shrink: 0;
        }
        
        .payment-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .payment-amount {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .btn-pay {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.95rem;
            padding: 0.75rem 1.5rem;
        }
        
        .btn-pay:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .checkout-layout {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .container {
                padding: 0.75rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .card {
                padding: 1rem;
            }
            
            .payment-amount {
                font-size: 1.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .address-item {
                padding: 0.75rem;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .form-input {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Checkout</h1>
            <p class="page-subtitle">Complete your order with secure payment</p>
        </div>
        
        <div class="checkout-layout">
            <!-- Saved Address Section -->
            <div class="card address-section">
                <h2 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Delivery Address
                </h2>
                
                <?php if($succ): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $succ ?>
                    </div>
                <?php endif; ?>
                
                <?php if($err): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= $err ?>
                    </div>
                <?php endif; ?>
                
                <form id="addressForm">
                    <div class="address-list">
                        <?php if ($num_addresses): ?>
                            <?php foreach($addresses as $a): ?>
                                <div class="address-item">
                                    <div class="address-content">
                                        <input type="radio" name="selected_address" value="<?= $a['address_id'] ?>" class="address-radio">
                                        <div class="address-details">
                                            <div class="address-type"><?= htmlspecialchars($a['address_type']) ?></div>
                                            <div class="address-line">
                                                <strong><?= htmlspecialchars($a['house_no']) ?></strong>
                                                <?= $a['landmark'] ? ', '.htmlspecialchars($a['landmark']) : '' ?>
                                            </div>
                                            <div class="address-line">
                                                <?= htmlspecialchars($a['city']) ?>, <?= htmlspecialchars($a['state']) ?> - <?= htmlspecialchars($a['pincode']) ?>
                                            </div>
                                            <div class="address-line">
                                                <i class="fas fa-phone"></i> <?= htmlspecialchars($a['mobile']) ?>
                                                <?php if ($a['alt_mobile']) echo " / ".htmlspecialchars($a['alt_mobile']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-address-msg">
                                <i class="fas fa-map-marker-alt" style="font-size: 1.5rem; margin-bottom: 0.5rem; opacity: 0.5;"></i><br>
                                No saved addresses found.<br>Please add a new address to continue.
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Payment Box -->
                <div class="payment-card card" id="payBox" style="display:<?= $num_addresses ? 'block' : 'none' ?>;">
                    <div class="payment-title">
                        <i class="fas fa-credit-card"></i> Total Amount
                    </div>
                    <div class="payment-amount">₹<span id="payAmt"><?= number_format($pay_amount,2) ?></span></div>
                    <button id="razorpayBtn" class="btn btn-pay">
                        <i class="fas fa-lock"></i> Secure Payment
                    </button>
                </div>
            </div>

            <!-- Add New Address Section -->
            <div class="card form-section">
                <h2 class="section-title">
                    <i class="fas fa-plus-circle"></i>
                    Add New Address
                </h2>
                
                <div class="form-container">
                    <form method="post" autocomplete="off">
                        <input type="hidden" name="new_address" value="1">
                        
                        <div class="form-group">
                            <label class="form-label">House/Flat/Building No.</label>
                            <input type="text" name="house_no" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Address Type</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="address_type" value="Home" id="home" required>
                                    <label for="home">Home</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="address_type" value="Office" id="office">
                                    <label for="office">Office</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Landmark</label>
                            <input type="text" name="landmark" class="form-input" placeholder="Nearby landmark for easy delivery">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-input" pattern="[0-9]{6}" maxlength="6" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile" class="form-input" pattern="[6-9][0-9]{9}" maxlength="10" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Alternative Mobile Number</label>
                            <input type="text" name="alt_mobile" class="form-input" pattern="[6-9][0-9]{9}" maxlength="10">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Address
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced address selection
        document.querySelectorAll('.address-item').forEach((item) => {
            const radio = item.querySelector('input[type="radio"]');
            
            item.addEventListener('click', function() {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            });
            
            radio.addEventListener('change', function() {
                // Remove selected class from all items
                document.querySelectorAll('.address-item').forEach(el => el.classList.remove('selected'));
                
                // Add selected class to current item
                if(this.checked) {
                    this.closest('.address-item').classList.add('selected');
                    
                    // Show payment box
                    const payBox = document.getElementById('payBox');
                    payBox.style.display = 'block';
                }
            });
        });

        // Enhanced Razorpay integration
        document.getElementById('razorpayBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            
            const selected = document.querySelector('input[name="selected_address"]:checked');
            if (!selected) {
                alert('Please select a delivery address to continue.');
                return;
            }
            
            const address_id = selected.value;
            
            // Add loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            this.disabled = true;
            
            const options = {
                "key": "<?= $keyId ?>",
                "amount": "<?= $pay_amount*100 ?>",
                "currency": "INR",
                "name": "GrocXpress",
                "description": "Order Payment",
                "image": "assets/logo.png",
                "handler": function (response) {
                    window.location = "payment_success.php?pid=" + encodeURIComponent(response.razorpay_payment_id)
                        + "&oid=" + encodeURIComponent(response.razorpay_order_id)
                        + "&sign=" + encodeURIComponent(response.razorpay_signature)
                        + "&address_id=" + encodeURIComponent(address_id);
                },
                "prefill": {
                    "name": "<?= htmlspecialchars($user['name']) ?>",
                    "email": "<?= htmlspecialchars($user['email']) ?>"
                },
                "modal": {
                    "ondismiss": () => {
                        // Reset button state if payment is cancelled
                        document.getElementById('razorpayBtn').innerHTML = '<i class="fas fa-lock"></i> Secure Payment';
                        document.getElementById('razorpayBtn').disabled = false;
                    }
                }
            };
            
            const rzp1 = new Razorpay(options);
            rzp1.open();
        });

        // Form validation enhancements
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#48bb78';
                } else if (this.value) {
                    this.style.borderColor = '#f56565';
                }
            });
            
            input.addEventListener('input', function() {
                if (this.style.borderColor === '#f56565' && this.checkValidity()) {
                    this.style.borderColor = '#667eea';
                }
            });
        });
        
        // Auto-show payment box if addresses exist
        if (<?= $num_addresses ?> > 0) {
            const firstAddress = document.querySelector('input[name="selected_address"]');
            if (firstAddress) {
                firstAddress.checked = true;
                firstAddress.closest('.address-item').classList.add('selected');
            }
        }
    </script>
</body>
</html>