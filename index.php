<?php 
session_start();
include 'includes/db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartMart - Your Smart Supermarket</title>
    <link rel="stylesheet" href="css/style.css">
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

<section class="hero">
    <h1>Welcome to <span>SmartMart</span></h1>
    <p>Your one stop shop for all your daily needs!</p>
    <div class="hero-buttons">
        <a href="products.php" class="btn-primary">Shop Now 🛒</a>
        <a href="about.php" class="btn-secondary">Learn More</a>
    </div>
</section>

<section class="section" style="background: #f0f4f8;">
    <h2 class="section-title">Shop by Category</h2>
    <p class="section-subtitle">Find what you need quickly!</p>
    <div class="categories-grid">
        <a href="products.php?category=1" class="category-card"><div class="icon">🥦</div><p>Vegetables</p></a>
        <a href="products.php?category=2" class="category-card"><div class="icon">🍎</div><p>Fruits</p></a>
        <a href="products.php?category=3" class="category-card"><div class="icon">🥛</div><p>Dairy</p></a>
        <a href="products.php?category=4" class="category-card"><div class="icon">🍞</div><p>Bakery</p></a>
        <a href="products.php?category=5" class="category-card"><div class="icon">🥩</div><p>Meat</p></a>
        <a href="products.php?category=6" class="category-card"><div class="icon">🥤</div><p>Beverages</p></a>
        <a href="products.php?category=7" class="category-card"><div class="icon">🧴</div><p>Personal Care</p></a>
        <a href="products.php?category=8" class="category-card"><div class="icon">🧹</div><p>Cleaning</p></a>
    </div>
</section>

<section class="section" style="background: white;">
    <h2 class="section-title">Featured Products</h2>
    <p class="section-subtitle">Best deals just for you!</p>
    <div class="products-grid">
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM products LIMIT 8");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($products) > 0) {
                foreach($products as $product) {
                    $img = $product['image'] ? 'images/'.$product['image'] : 'https://via.placeholder.com/300x180?text='.urlencode($product['name']);
                    echo '<div class="product-card">
                        <img src="'.$img.'" alt="'.$product['name'].'" onerror="this.src=\'https://via.placeholder.com/300x180?text=No+Image\'">
                        <div class="product-info">
                            <h3>'.$product['name'].'</h3>
                            <p class="price">LKR '.number_format($product['price'], 2).'</p>
                            <button class="add-cart" onclick="addToCart('.$product['id'].', \''.$product['name'].'\', '.$product['price'].')">Add to Cart 🛒</button>
                        </div>
                    </div>';
                }
            } else {
                echo '<p style="text-align:center; color:#666; grid-column:1/-1;">No products yet! <a href="admin/products.php" style="color:#1a73e8;">Add products</a></p>';
            }
        } catch(Exception $e) {
            echo '<p>Products loading...</p>';
        }
        ?>
    </div>
</section>

<footer class="footer">
    <h3>Smart<span style="color:#ffd700">Mart</span></h3>
    <p>📍 123 Main Street, Colombo, Sri Lanka</p>
    <p>📞 +94 11 234 5678</p>
    <p>📧 info@smartmart.lk</p>
    <div class="footer-bottom"><p>© 2026 SmartMart. All Rights Reserved.</p></div>
</footer>

<script>
function addToCart(id, name, price) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let existing = cart.find(item => item.id === id);
    if(existing) { existing.quantity += 1; }
    else { cart.push({id: id, name: name, price: price, quantity: 1}); }
    localStorage.setItem('cart', JSON.stringify(cart));
    alert(name + ' added to cart! 🛒');
}
</script>
</body>
</html>
