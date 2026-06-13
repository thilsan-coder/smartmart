<?php
session_start();
include 'includes/db.php';
$category_id = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
try {
    $cat_stmt = $pdo->query("SELECT * FROM categories");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    if($category_id && $search) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.name LIKE ?");
        $stmt->execute([$category_id, "%$search%"]);
    } elseif($category_id) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ?");
        $stmt->execute([$category_id]);
    } elseif($search) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ?");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $products = []; $categories = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - SmartMart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .products-page { padding:40px; max-width:1200px; margin:0 auto; }
        .page-title { font-size:32px; font-weight:800; color:#1a73e8; margin-bottom:30px; }
        .filter-bar { display:flex; gap:15px; margin-bottom:30px; flex-wrap:wrap; align-items:center; }
        .filter-bar input { padding:10px 20px; border:2px solid #e0e0e0; border-radius:25px; font-size:15px; outline:none; flex:1; min-width:200px; transition:0.3s; }
        .filter-bar input:focus { border-color:#1a73e8; }
        .filter-bar select { padding:10px 20px; border:2px solid #e0e0e0; border-radius:25px; font-size:15px; outline:none; cursor:pointer; }
        .filter-bar button { padding:10px 25px; background:#1a73e8; color:white; border:none; border-radius:25px; font-size:15px; font-weight:600; cursor:pointer; }
        .filter-bar button:hover { background:#0d47a1; }
        .products-count { color:#666; margin-bottom:20px; font-size:15px; }
        .products-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:25px; }
        .product-card { background:white; border-radius:15px; overflow:hidden; box-shadow:0 3px 15px rgba(0,0,0,0.08); transition:0.3s; }
        .product-card:hover { transform:translateY(-5px); box-shadow:0 8px 25px rgba(26,115,232,0.2); }
        .product-card img { width:100%; height:180px; object-fit:cover; }
        .product-info { padding:15px; }
        .category-badge { font-size:12px; color:#1a73e8; font-weight:600; margin-bottom:5px; }
        .product-info h3 { font-size:16px; font-weight:600; margin-bottom:8px; }
        .stock-badge { display:inline-block; padding:3px 10px; border-radius:10px; font-size:12px; font-weight:600; margin-bottom:8px; }
        .in-stock { background:#e0f7e9; color:#2e7d32; }
        .out-stock { background:#ffe0e0; color:#d32f2f; }
        .price { color:#1a73e8; font-size:18px; font-weight:700; margin-bottom:10px; }
        .add-cart { background:#1a73e8; color:white; border:none; padding:8px 20px; border-radius:20px; cursor:pointer; font-size:14px; width:100%; transition:0.3s; }
        .add-cart:hover { background:#0d47a1; }
        .no-products { text-align:center; padding:60px; color:#666; font-size:18px; grid-column:1/-1; }
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
<div class="products-page">
    <h1 class="page-title">🛒 Our Products</h1>
    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="🔍 Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Search</button>
        <a href="products.php" style="color:#666; font-size:14px; padding:10px;">Clear</a>
    </form>
    <p class="products-count">Showing <strong><?php echo count($products); ?></strong> products</p>
    <div class="products-grid">
        <?php if(count($products) > 0): ?>
            <?php foreach($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo $product['image'] ? 'images/'.$product['image'] : 'https://via.placeholder.com/300x180?text='.urlencode($product['name']); ?>" 
                     alt="<?php echo $product['name']; ?>"
                     onerror="this.src='https://via.placeholder.com/300x180?text=No+Image'">
                <div class="product-info">
                    <p class="category-badge"><?php echo $product['category_name'] ?? 'General'; ?></p>
                    <h3><?php echo $product['name']; ?></h3>
                    <span class="stock-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                        <?php echo $product['stock'] > 0 ? '✅ In Stock ('.$product['stock'].')' : '❌ Out of Stock'; ?>
                    </span>
                    <p class="price">LKR <?php echo number_format($product['price'], 2); ?></p>
                    <?php if($product['stock'] > 0): ?>
                        <button class="add-cart" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>)">Add to Cart 🛒</button>
                    <?php else: ?>
                        <button class="add-cart" style="background:#ccc;cursor:not-allowed;">Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products"><p>😕 No products found!</p><a href="products.php" style="color:#1a73e8;">View all products</a></div>
        <?php endif; ?>
    </div>
</div>
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
