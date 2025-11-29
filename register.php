<?php
include "includes/db_connect.php";
$err = $succ = "";

if (isset($_POST['register'])) {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $pass       = $_POST['password'];
    $phone      = trim($_POST['phone']);
    $gender     = $_POST['gender'] ?? '';
    $dob        = $_POST['dob'] ?? '';
    $address    = trim($_POST['address']);
    $city       = trim($_POST['city']);
    $state      = trim($_POST['state']);
    $pin        = trim($_POST['pin']);

    $name_pattern = '/^[A-Z][a-zA-Z]{1,29}$/';

    // Server-side validations
    if (!preg_match($name_pattern, $first_name)) {
        $err = "First name must start with a capital letter and contain only letters (no spaces).";
    } elseif (!preg_match($name_pattern, $last_name)) {
        $err = "Last name must start with a capital letter and contain only letters (no spaces).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Please enter a valid email address.";
    } elseif (strlen($pass) < 8) {
        $err = "Password must be at least 8 characters.";
    } elseif (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $err = "Please enter a valid 10-digit phone number (starts with 6/7/8/9).";
    } elseif (!in_array($gender, ['Male', 'Female', 'Other'])) {
        $err = "Please select a gender.";
    } elseif (!$dob) {
        $err = "Please enter Date of Birth.";
    } elseif (!$address || !$city || !$state) {
        $err = "Please fill all address fields.";
    } elseif (!preg_match('/^[0-9]{6}$/', $pin)) {
        $err = "Please enter a valid 6-digit pin code.";
    } else {
        $password_hashed = md5($pass); // For real-world use, use password_hash() for security
        $q = "INSERT INTO users 
            (first_name, last_name, email, password, phone, gender, dob, address, city, state, pin, is_admin) VALUES (
            '$first_name','$last_name','$email','$password_hashed','$phone','$gender','$dob',
            '$address','$city','$state','$pin',0)";
        if (mysqli_query($conn, $q)) {
            $succ = "Registration successful! Please <a href='login.php'>login</a>.";
        } else {
            $err = "Registration failed (maybe email or phone already in use).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<style>
    /* ----- General Body & Font Styles ----- */
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f0f4f0; /* Very light green background */
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
        box-sizing: border-box;
    }

    /* ----- Main Registration Container ----- */
    .register-container {
        background-color: #ffffff; /* Clean white background */
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 800px;
        box-sizing: border-box;
    }

    .register-container h2 {
        color: #2e8b57; /* Sea green for heading */
        text-align: center;
        margin-bottom: 30px;
        font-weight: 600;
    }

    .register-container h2 .fas {
        margin-right: 10px;
    }

    /* ----- Form Grid Layout ----- */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Two-column layout */
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group-full {
        grid-column: 1 / -1; /* Make element span both columns */
    }

    /* ----- Form Element Styling ----- */
    label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
    }

    .input-field,
    .select-field,
    .textarea-field {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
        color: #333;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .input-field::placeholder,
    .textarea-field::placeholder {
        color: #aaa;
    }

    .input-field:focus,
    .select-field:focus,
    .textarea-field:focus {
        outline: none;
        border-color: #2e8b57; /* Highlight with main green on focus */
        box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.15);
    }

    .textarea-field {
        resize: vertical;
        min-height: 80px;
    }

    /* ----- Button Styling ----- */
    .register-button {
        background-color: #2e8b57; /* Main sea green */
        color: #ffffff;
        border: none;
        padding: 15px;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .register-button:hover {
        background-color: #256d45; /* Darker green on hover */
        transform: translateY(-2px);
    }

    /* ----- Links & Messages ----- */
    .login-link {
        display: block;
        text-align: center;
        margin-top: 25px;
        color: #2e8b57;
        text-decoration: none;
        font-weight: 500;
    }

    .login-link:hover {
        text-decoration: underline;
    }

    .message-box {
        padding: 15px;
        margin-top: 20px;
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .success-message {
        background-color: #d4edda; /* Light green for success */
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .success-message a {
        color: #155724;
        font-weight: 700;
    }

    /* ----- Responsive Design ----- */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr; /* Single column on smaller screens */
        }
        .register-container {
            padding: 25px;
        }
    }
</style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GrocXpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Retain your entire style here (unchanged) */
        /* ... [PASTE YOUR EXISTING STYLE CODE HERE] ... */
    </style>

    <script>
        function validateForm() {
            const f = document.forms["regForm"];
            const firstName = f["first_name"].value.trim();
            const lastName = f["last_name"].value.trim();
            const email = f["email"].value.trim();
            const password = f["password"].value;
            const phone = f["phone"].value;
            const pin = f["pin"].value;
            const dob = f["dob"].value;

            let nameTest = /^[A-Z][a-zA-Z]{1,29}$/;
            let emailTest = /^[a-zA-Z0-9._+-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,}$/;
            let phoneTest = /^[6-9][0-9]{9}$/;
            let pinTest = /^[0-9]{6}$/;

            if (!nameTest.test(firstName)) {
                alert("First name must start with a capital letter, contain only letters, and be 2-30 characters long.");
                return false;
            }
            if (!nameTest.test(lastName)) {
                alert("Last name must start with a capital letter, contain only letters, and be 2-30 characters long.");
                return false;
            }
            if (!emailTest.test(email)) {
                alert("Please enter a valid email address.");
                return false;
            }
            if (password.length < 8) {
                alert("Password must be at least 8 characters long.");
                return false;
            }
            if (!phoneTest.test(phone)) {
                alert("Please enter a valid 10-digit phone number starting with 6, 7, 8, or 9.");
                return false;
            }
            if (!pinTest.test(pin)) {
                alert("Please enter a valid 6-digit pin code.");
                return false;
            }
            if (!dob) {
                alert("Please enter your date of birth.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="register-container">
        <h2><i class="fas fa-user-plus"></i> Create Your Account</h2>
        <form method="post" name="regForm" autocomplete="off" onsubmit="return validateForm()">
            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="input-field" placeholder="First Name" required pattern="^[A-Z][a-zA-Z]{1,29}$" title="First letter uppercase, 2-30 letters, no spaces">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="input-field" placeholder="Last Name" required pattern="^[A-Z][a-zA-Z]{1,29}$" title="First letter uppercase, 2-30 letters, no spaces">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="input-field" placeholder="Email" required maxlength="60">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="input-field" placeholder="Password (min 8 chars)" required minlength="8" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" class="input-field" placeholder="Phone (10 digits)" required minlength="10" maxlength="10" pattern="[6-9]{1}[0-9]{9}" title="Enter a valid 10-digit phone starting with 6-9">
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender" class="select-field" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group form-group-full">
                    <label for="dob">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="input-field" required>
                </div>
                <div class="form-group form-group-full">
                    <label for="address">Street Address</label>
                    <textarea name="address" id="address" class="textarea-field" placeholder="Street address" required></textarea>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" class="input-field" placeholder="City" required>
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" name="state" id="state" class="input-field" placeholder="State" required>
                </div>
                <div class="form-group">
                    <label for="pin">Pin Code</label>
                    <input type="text" name="pin" id="pin" class="input-field" placeholder="Pin Code" required pattern="[0-9]{6}" maxlength="6">
                </div>
                <div class="form-group form-group-full">
                    <button type="submit" name="register" class="register-button">Create Account</button>
                </div>
            </div>
            <?php if (!empty($err)): ?>
                <div class="message-box error-message"><?php echo htmlspecialchars($err); ?></div>
            <?php endif; ?>
            <?php if (!empty($succ)): ?>
                <div class="message-box success-message"><?php echo $succ; ?></div>
            <?php endif; ?>
            <a href="login.php" class="login-link">Already have an account? Log in</a>
        </form>
    </div>
</body>
</html>
