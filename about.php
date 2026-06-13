<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - SmartMart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .about-hero { background:linear-gradient(135deg,#1a73e8,#0d47a1); color:white; padding:80px 40px; text-align:center; }
        .about-hero h1 { font-size:42px; font-weight:800; margin-bottom:15px; }
        .about-hero h1 span { color:#ffd700; }
        .about-hero p { font-size:18px; opacity:0.9; }
        .about-page { max-width:1100px; margin:0 auto; padding:60px 40px; }
        .about-section { background:white; border-radius:15px; padding:40px; box-shadow:0 3px 15px rgba(0,0,0,0.08); margin-bottom:25px; }
        .about-section h2 { font-size:26px; font-weight:800; color:#1a73e8; margin-bottom:15px; }
        .about-section p { color:#555; font-size:16px; line-height:1.8; margin-bottom:10px; }
        .features-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-top:20px; }
        .feature-card { background:#f0f4f8; border-radius:12px; padding:25px; text-align:center; }
        .feature-card .icon { font-size:40px; margin-bottom:10px; }
        .feature-card h3 { font-size:16px; font-weight:700; color:#333; margin-bottom:8px; }
        .feature-card p { font-size:14px; color:#666; margin:0; }
        .team-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; margin-top:20px; }
        .team-card { background:#f0f4f8; border-radius:12px; padding:25px; text-align:center; }
        .team-card .avatar { font-size:50px; margin-bottom:10px; }
        .team-card h3 { font-size:16px; font-weight:700; color:#333; }
        .team-card p { font-size:13px; color:#1a73e8; font-weight:600; margin:0; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">Smart<span>Mart</span></div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="products.php">Products</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
    <div class="nav-right">
        <a href="cart.php">🛒 Cart</a>
        <?php if(isset($_SESSION['user_name'])): ?>
            <span style="color:white;">Hi, <?php echo $_SESSION['user_name']; ?>!</span>
            <a href="logout.php" class="btn-login">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-register">Register</a>
        <?php endif; ?>
    </div>
</nav>
<section class="about-hero">
    <h1>About <span>SmartMart</span></h1>
    <p>Your trusted supermarket for quality products at great prices!</p>
</section>
<div class="about-page">
    <div class="about-section">
        <h2>🏪 Who We Are</h2>
        <p>SmartMart is a modern supermarket dedicated to providing fresh, quality products to our valued customers. We believe that shopping should be easy, affordable, and enjoyable.</p>
        <p>Founded in 2020, we have grown to become one of the most trusted supermarkets in Sri Lanka, serving thousands of happy customers every day!</p>
    </div>
    <div class="about-section">
        <h2>⭐ Why Choose Us?</h2>
        <div class="features-grid">
            <div class="feature-card"><div class="icon">🥦</div><h3>Fresh Products</h3><p>Always fresh, always quality!</p></div>
            <div class="feature-card"><div class="icon">💰</div><h3>Best Prices</h3><p>Unbeatable prices every day!</p></div>
            <div class="feature-card"><div class="icon">🚀</div><h3>Fast Service</h3><p>Quick checkout & delivery!</p></div>
            <div class="feature-card"><div class="icon">😊</div><h3>Friendly Staff</h3><p>Always here to help you!</p></div>
        </div>
    </div>
    <div class="about-section">
        <h2>👥 Our Team</h2>
        <div class="team-grid">
            <div class="team-card"><div class="avatar">👨‍💼</div><h3>John Silva</h3><p>Manager</p></div>
            <div class="team-card"><div class="avatar">👩‍💼</div><h3>Mary Fernando</h3><p>Assistant Manager</p></div>
            <div class="team-card"><div class="avatar">👨‍💻</div><h3>Kamal Perera</h3><p>IT Manager</p></div>
            <div class="team-card"><div class="avatar">👩‍🍳</div><h3>Nisha Raj</h3><p>Products Manager</p></div>
        </div>
    </div>
</div>
<footer class="footer">
    <h3>Smart<span style="color:#ffd700">Mart</span></h3>
    <p>📍 123 Main Street, Colombo, Sri Lanka</p>
    <p>📞 +94 11 234 5678</p>
    <p>📧 info@smartmart.lk</p>
    <div class="footer-bottom"><p>© 2026 SmartMart. All Rights Reserved.</p></div>
</footer>
</body>
</html>
