<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrocXpress - Online Shopping for Groceries & Electronics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/GrocXpress/assets/style.css">
    <script src="/GrocXpress/assets/script.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50; /* Modern Green */
            --secondary-color: #2196F3; /* Complementary Blue */
            --background-light: #f4f6f8; /* Light gray background */
            --background-dark: #2c3e50; /* Dark blue background */
            --text-light: #333;
            --text-dark: #ecf0f1;
            --card-background-light: #ffffff;
            --card-background-dark: #34495e;
            --border-color-light: #e0e0e0;
            --border-color-dark: #555;
            --shadow-light: rgba(0, 0, 0, 0.08);
            --shadow-dark: rgba(0, 0, 0, 0.3);
            --transition-speed: 0.3s ease;
        }

        /* Dark Mode */
        body.dark-mode {
            background-color: var(--background-dark);
            color: var(--text-dark);
        }
        body.dark-mode .header {
            background-color: var(--card-background-dark);
            color: var(--text-dark);
            box-shadow: 0 4px 15px var(--shadow-dark);
        }
        body.dark-mode .auth-buttons a {
            color: var(--text-dark);
            border-color: var(--primary-color);
        }
        body.dark-mode .auth-buttons a:hover,
        body.dark-mode #theme-toggle:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--text-dark);
        }
        body.dark-mode .hero::before {
            background: rgba(0, 0, 0, 0.6);
        }
        body.dark-mode .section-title {
            color: var(--primary-color);
        }
        body.dark-mode .category-card {
            background-color: var(--card-background-dark);
            box-shadow: 0 4px 20px var(--shadow-dark);
            border: 1px solid var(--border-color-dark);
        }
        body.dark-mode .category-card:hover {
            box-shadow: 0 10px 30px var(--shadow-dark);
        }
        body.dark-mode .category-title {
            color: var(--primary-color);
        }
        body.dark-mode .category-desc {
            color: var(--text-dark);
        }
        body.dark-mode footer {
            background-color: var(--card-background-dark);
            border-top: 1px solid var(--border-color-dark);
        }
        body.dark-mode .footer-column a {
            color: var(--text-dark);
        }
        body.dark-mode .footer-column a:hover {
            color: var(--primary-color);
        }
        body.dark-mode .social-links a {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-dark);
        }
        body.dark-mode .social-links a:hover {
            background: var(--primary-color);
        }


        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: var(--background-light);
            color: var(--text-light);
            line-height: 1.6;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        .header {
            background: var(--card-background-light);
            color: var(--text-light);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-color-light);
        }

        .header .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--primary-color);
        }
        .header .logo i {
            font-size: 2.5rem;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        .header .logo h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-light);
        }
        .header .tagline {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: -5px;
            color: var(--text-light);
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        .auth-buttons a {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border-radius: 25px;
            text-decoration: none;
            transition: all var(--transition-speed);
            border: none;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.2);
        }
        .auth-buttons a:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(33, 150, 243, 0.3);
        }
        /* theme toggle removed */


        .hero {
            min-height: 70vh;
            background: url('https://images.unsplash.com/photo-1542838132-92c441eea23f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80') no-repeat center center/cover;
            display: flex; flex-direction: column; justify-content: center; align-items: flex-start;
            padding: 0 5%;
            color: white;
            position: relative;
            box-shadow: 0 8px 20px var(--shadow-light);
        }
        .hero::before {
            content: '';
            position: absolute; top:0; left:0; width:100%; height:100%;
            background: rgba(0, 0, 0, 0.55);
        }
        .hero-content {
            position: relative;
            max-width: 700px;
            z-index: 1;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .hero h2 {
            font-size: 3.8rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
        }
        .btn {
            display: inline-block;
            padding: 14px 35px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            transition: all var(--transition-speed);
            text-decoration: none;
            color: #fff;
            background: var(--primary-color);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }
        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
        }

        .categories, .features {
            padding: 4rem 2rem;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .section-title {
            font-size: 3rem;
            margin-bottom: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .category-grid, .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-top: 2rem;
        }
        .category-card, .feature-card {
            background: var(--card-background-light);
            border-radius: 15px;
            box-shadow: 0 8px 25px var(--shadow-light);
            transition: all var(--transition-speed);
            overflow: hidden;
            border: 1px solid var(--border-color-light);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 1.5rem;
        }
        .category-card:hover, .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px var(--shadow-light);
        }
        .category-img {
            height: 200px;
            width: 100%;
            overflow: hidden;
            border-bottom: 1px solid var(--border-color-light);
        }
        .category-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-speed);
        }
        .category-card:hover .category-img img {
            transform: scale(1.1);
        }
        .category-content {
            padding: 1.5rem;
            text-align: center;
            flex-grow: 1;
        }
        .category-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.8rem;
        }
        .category-desc {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-size: 1rem;
            opacity: 0.8;
        }

        .feature-card {
            padding: 2rem;
            text-align: center;
        }
        .feature-card i {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        .feature-card h3 {
            font-size: 1.8rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .feature-card p {
            color: var(--text-light);
            font-size: 1rem;
            opacity: 0.8;
        }


        footer {
            background: var(--card-background-light);
            color: var(--text-light);
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid var(--border-color-light);
            box-shadow: 0 -4px 15px var(--shadow-light);
        }
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: left;
        }
        .footer-column h4 {
            font-size: 1.3rem;
            margin-bottom: 1.2rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        .footer-column p, .footer-column a {
            margin-bottom: 0.8rem;
            opacity: 0.8;
            font-size: 0.95rem;
            color: var(--text-light);
            text-decoration: none;
            display: block;
            transition: color var(--transition-speed);
        }
        .footer-column a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        .social-links {
            display: flex;
            gap: 1rem;
            justify-content: flex-start; /* Changed from center to flex-start */
            margin-top: 1rem;
        }
        .social-links a {
            display: inline-flex; align-items: center; justify-content: center;
            width: 45px; height: 45px;
            background: rgba(76, 175, 80, 0.1);
            border-radius: 50%;
            color: var(--primary-color);
            font-size: 1.4em;
            transition: background var(--transition-speed), transform var(--transition-speed), color var(--transition-speed);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .social-links a:hover {
            background: var(--primary-color);
            transform: scale(1.1);
            color: white;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }
        .copyright {
            margin-top: 2rem;
            font-size: 0.9rem;
            opacity: 0.7;
        }

        @media (max-width: 992px) {
            .header { padding: 1rem 1.5rem; }
            .header .logo h1 { font-size: 1.8rem; }
            .hero h2 { font-size: 3rem; }
            .hero p { font-size: 1.1rem; }
            .btn { padding: 12px 30px; font-size: 1.1rem; }
            .section-title { font-size: 2.5rem; margin-bottom: 2.5rem;}
            .category-title, .feature-card h3 { font-size: 1.6rem; }
            .category-desc, .feature-card p { font-size: 0.95rem; }
        }

        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 1rem; padding: 1rem; }
            .auth-buttons { margin-top: 1rem; }
            .hero { text-align: center; align-items: center; padding: 0 1rem; }
            .hero-content { max-width: 90%; }
            .hero h2 { font-size: 2.5rem; }
            .hero p { font-size: 1rem; }
            .section-title { font-size: 2rem; margin-bottom: 2rem; }
            .category-grid, .features-grid { grid-template-columns: 1fr; }
            .footer-content { grid-template-columns: 1fr; text-align: center; }
            .social-links { justify-content: center; }
        }

        @media (max-width: 480px) {
            .header .logo h1 { font-size: 1.5rem; }
            .header .tagline { font-size: 0.8rem; }
            .auth-buttons a { padding: 8px 15px; font-size: 0.9rem; }
            .hero h2 { font-size: 2rem; margin-bottom: 1rem; }
            .hero p { font-size: 0.9rem; margin-bottom: 1.5rem; }
            .btn { padding: 10px 25px; font-size: 1rem; }
            .section-title { font-size: 1.8rem; }
            .category-title, .feature-card h3 { font-size: 1.4rem; }
            .category-desc, .feature-card p { font-size: 0.9rem; }
            .footer-column h4 { font-size: 1.1rem; }
            .footer-column p, .footer-column a { font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="#" class="logo">
            <i class="fas fa-shopping-basket"></i>
            <div>
                <h1>GrocXpress</h1>
                <p class="tagline">Your one-stop shop</p>
            </div>
        </a>
        <div class="auth-buttons">
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
            <button id="theme-toggle"><i class="fas fa-moon"></i></button>
        </div>
    </div>

    <section class="hero">
        <div class="hero-content">
            <h2>Fresh Groceries & Top-Tier Electronics</h2>
            <p>Discover a seamless shopping experience with thousands of quality products, delivered swiftly to your home. Your satisfaction, our priority.</p>
            <a href="login.php" class="btn">Shop Now <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

    <section class="features">
        <h2 class="section-title">Why Choose GrocXpress?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-truck-fast"></i>
                <h3>Rapid Delivery</h3>
                <p>Get your essentials delivered to your doorstep in record time. Freshness and speed guaranteed!</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-hand-holding-heart"></i>
                <h3>Quality Assurance</h3>
                <p>We handpick every item to ensure you receive only the best. Shop with confidence.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Our dedicated customer service team is always here to assist you, day or night.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-tags"></i>
                <h3>Best Prices</h3>
                <p>Enjoy competitive pricing and amazing deals on all your favorite products.</p>
            </div>
        </div>
    </section>

    <section class="categories">
        <h2 class="section-title">Explore Our Categories</h2>
        <div class="category-grid">
            <div class="category-card">
                <div class="category-img">
                    <img src="https://images.unsplash.com/photo-1542838132-92c441eea23f?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Fresh fruits and vegetables arranged in colorful display at a grocery store">
                </div>
                <div class="category-content">
                    <h3 class="category-title">Fresh Grocery</h3>
                    <p class="category-desc">Farm fresh fruits, vegetables, organic dairy, and more for a healthy lifestyle.</p>
                    <a href="#" class="btn btn-small">Discover More</a>
                </div>
            </div>
            <div class="category-card">
                <div class="category-img">
                    <img src="https://images.unsplash.com/photo-1590483489823-3ae79f2258ca?q=80&w=1935&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Modern kitchen appliances including blender, toaster and coffee maker on marble countertop">
                </div>
                <div class="category-content">
                    <h3 class="category-title">Home Appliances</h3>
                    <p class="category-desc">Innovative kitchen and home appliances to make your daily life easier and smarter.</p>
                    <a href="#" class="btn btn-small">Discover More</a>
                </div>
            </div>
            <div class="category-card">
                <div class="category-img">
                    <img src="https://images.unsplash.com/photo-1517336714731-489689fd1ca8?q=80&w=1726&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Collection of smartphones, tablets and laptops displayed on clean white background">
                </div>
                <div class="category-content">
                    <h3 class="category-title">Electronics</h3>
                    <p class="category-desc">The latest gadgets, from smartphones to smart home devices, at unbeatable prices.</p>
                    <a href="#" class="btn btn-small">Discover More</a>
                </div>
            </div>
            <div class="category-card">
                <div class="category-img">
                    <img src="https://images.unsplash.com/photo-1543168256-418811576926?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Assortment of packaged food items including cereals, snacks and pasta on shelf">
                </div>
                <div class="category-content">
                    <h3 class="category-title">Pantry Essentials</h3>
                    <p class="category-desc">Stock up on everyday necessities and gourmet treats for your pantry.</p>
                    <a href="#" class="btn btn-small">Discover More</a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h4>GrocXpress</h4>
                <p>Your ultimate destination for online grocery and electronics shopping.</p>
                <a href="#">About Us</a>
                <a href="#">Careers</a>
            </div>
            <div class="footer-column">
                <h4>Customer Service</h4>
                <a href="#">Help Center</a>
                <a href="#">Returns & Refunds</a>
                <a href="#">Shipping Information</a>
                <a href="#">Privacy Policy</a>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <a href="#">Today's Deals</a>
                <a href="#">New Arrivals</a>
                <a href="#">Gift Cards</a>
                <a href="#">Blog</a>
            </div>
            <div class="footer-column">
                <h4>Connect With Us</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <p style="margin-top: 1.5rem;">Email: support@grocxpress.com</p>
            </div>
        </div>
        <div class="copyright">
            &copy; 2023 GrocXpress. All rights reserved.
        </div>
    </footer>

    <script>
        // Legacy index toggle: keep for backward compatibility.
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;

            // Check for saved theme preference (legacy key or new)
            const savedTheme = localStorage.getItem('grocxpress:theme') || localStorage.getItem('theme');
            if (savedTheme) {
                if (savedTheme === 'dark' || savedTheme === 'dark-mode' || savedTheme === '1') {
                    body.classList.add('dark-mode');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                } else {
                    body.classList.add('light-mode');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                }
            } else {
                body.classList.add('light-mode');
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }

            themeToggle.addEventListener('click', () => {
                if (body.classList.contains('dark-mode')) {
                    body.classList.remove('dark-mode');
                    body.classList.add('light-mode');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                    localStorage.setItem('grocxpress:theme', 'light');
                } else {
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                    localStorage.setItem('grocxpress:theme', 'dark');
                }
            });
        });
    </script>
</body>
</html>