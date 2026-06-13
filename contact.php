<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - SmartMart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .contact-hero { background:linear-gradient(135deg,#1a73e8,#0d47a1); color:white; padding:80px 40px; text-align:center; }
        .contact-hero h1 { font-size:42px; font-weight:800; margin-bottom:15px; }
        .contact-hero h1 span { color:#ffd700; }
        .contact-hero p { font-size:18px; opacity:0.9; }
        .contact-page { max-width:1100px; margin:0 auto; padding:60px 40px; display:grid; grid-template-columns:1fr 1fr; gap:30px; }
        @media(max-width:768px) { .contact-page { grid-template-columns:1fr; } }
        .contact-info, .contact-form { background:white; border-radius:15px; padding:35px; box-shadow:0 3px 15px rgba(0,0,0,0.08); }
        .contact-info h2, .contact-form h2 { font-size:24px; font-weight:800; color:#1a73e8; margin-bottom:25px; }
        .info-item { display:flex; align-items:flex-start; gap:15px; margin-bottom:25px; }
        .info-icon { font-size:28px; min-width:40px; }
        .info-text h3 { font-size:16px; font-weight:700; color:#333; margin-bottom:5px; }
        .info-text p { font-size:14px; color:#666; line-height:1.6; }
        .form-group { margin-bottom:18px; }
        .form-group label { display:block; font-size:14px; font-weight:600; color:#333; margin-bottom:8px; }
        .form-group input, .form-group textarea { width:100%; padding:12px 15px; border:2px solid #e0e0e0; border-radius:10px; font-size:15px; outline:none; transition:0.3s; font-family:inherit; }
        .form-group input:focus, .form-group textarea:focus { border-color:#1a73e8; }
        .form-group textarea { resize:none; height:120px; }
        .btn-send { width:100%; padding:14px; background:#1a73e8; color:white; border:none; border-radius:10px; font-size:16px; font-weight:700; cursor:pointer; transition:0.3s; }
        .btn-send:hover { background:#0d47a1; }
        .success-msg { background:#e0f7e9; color:#2e7d32; padding:12px 20px; border-radius:10px; margin-bottom:20px; font-weight:600; text-align:center; }
        .opening-hours { background:white; border-radius:15px; padding:35px; box-shadow:0 3px 15px rgba(0,0,0,0.08); grid-column:1/-1; }
        .opening-hours h2 { font-size:24px; font-weight:800; color:#1a73e8; margin-bottom:20px; }
        .hours-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:15px; }
        .hours-card { background:#f0f4f8; border-radius:12px; padding:20px; text-align:center; }
        .hours-card h3 { font-size:15px; font-weight:700; color:#333; margin-bottom:8px; }
        .hours-card p { font-size:14px; color:#1a73e8; font-weight:600; }
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
<section class="contact-hero">
    <h1>Contact <span>Us</span></h1>
    <p>We'd love to hear from you!</p>
</section>
<div class="contact-page">
    <div class="contact-info">
        <h2>📍 Find Us</h2>
        <div class="info-item"><div class="info-icon">📍</div><div class="info-text"><h3>Address</h3><p>123 Main Street,<br>Colombo 03, Sri Lanka</p></div></div>
        <div class="info-item"><div class="info-icon">📞</div><div class="info-text"><h3>Phone</h3><p>+94 11 234 5678<br>+94 77 123 4567</p></div></div>
        <div class="info-item"><div class="info-icon">📧</div><div class="info-text"><h3>Email</h3><p>info@smartmart.lk</p></div></div>
        <div class="info-item"><div class="info-icon">🌐</div><div class="info-text"><h3>Website</h3><p>www.smartmart.lk</p></div></div>
    </div>
    <div class="contact-form">
        <h2>✉️ Send Message</h2>
        <?php if(isset($_GET['sent'])): ?><div class="success-msg">✅ Message sent successfully!</div><?php endif; ?>
        <form method="GET">
            <div class="form-group"><label>Your Name</label><input type="text" name="name" placeholder="Enter your name" required></div>
            <div class="form-group"><label>Email Address</label><input type="email" name="email" placeholder="Enter your email" required></div>
            <div class="form-group"><label>Phone Number</label><input type="text" name="phone" placeholder="Enter your phone"></div>
            <div class="form-group"><label>Message</label><textarea name="message" placeholder="Write your message here..." required></textarea></div>
            <input type="hidden" name="sent" value="1">
            <button type="submit" class="btn-send">📨 Send Message</button>
        </form>
    </div>
    <div class="opening-hours">
        <h2>🕐 Opening Hours</h2>
        <div class="hours-grid">
            <div class="hours-card"><h3>Monday</h3><p>7:00 AM - 10:00 PM</p></div>
            <div class="hours-card"><h3>Tuesday</h3><p>7:00 AM - 10:00 PM</p></div>
            <div class="hours-card"><h3>Wednesday</h3><p>7:00 AM - 10:00 PM</p></div>
            <div class="hours-card"><h3>Thursday</h3><p>7:00 AM - 10:00 PM</p></div>
            <div class="hours-card"><h3>Friday</h3><p>7:00 AM - 11:00 PM</p></div>
            <div class="hours-card"><h3>Saturday</h3><p>8:00 AM - 11:00 PM</p></div>
            <div class="hours-card"><h3>Sunday</h3><p>9:00 AM - 9:00 PM</p></div>
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
