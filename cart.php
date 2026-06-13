<?php session_start(); include 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - SmartMart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-page { padding:40px; max-width:1000px; margin:0 auto; }
        .cart-title { font-size:32px; font-weight:800; color:#1a73e8; margin-bottom:30px; }
        .cart-table { background:white; border-radius:15px; padding:25px; box-shadow:0 3px 15px rgba(0,0,0,0.08); margin-bottom:20px; overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        thead { background:#1a73e8; color:white; }
        thead th { padding:12px 15px; text-align:left; font-size:14px; }
        tbody tr { border-bottom:1px solid #f0f0f0; }
        tbody td { padding:12px 15px; font-size:14px; }
        .qty-btn { background:#1a73e8; color:white; border:none; width:28px; height:28px; border-radius:50%; font-size:16px; cursor:pointer; }
        .qty-display { display:inline-block; width:35px; text-align:center; font-weight:700; }
        .btn-remove { background:#ff4444; color:white; border:none; padding:6px 15px; border-radius:8px; font-size:13px; cursor:pointer; }
        .cart-summary { background:white; border-radius:15px; padding:25px; box-shadow:0 3px 15px rgba(0,0,0,0.08); }
        .cart-summary h2 { font-size:20px; font-weight:700; color:#333; margin-bottom:20px; }
        .summary-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0f0f0; font-size:15px; }
        .summary-row.total { font-size:20px; font-weight:800; color:#1a73e8; border-bottom:none; margin-top:10px; }
        .btn-checkout { width:100%; padding:15px; background:#1a73e8; color:white; border:none; border-radius:12px; font-size:17px; font-weight:700; cursor:pointer; margin-top:20px; transition:0.3s; }
        .btn-checkout:hover { background:#0d47a1; }
        .btn-clear { width:100%; padding:12px; background:#ff4444; color:white; border:none; border-radius:12px; font-size:15px; font-weight:600; cursor:pointer; margin-top:10px; }
        .empty-cart { text-align:center; padding:60px; color:#666; }
        .empty-cart a { background:#1a73e8; color:white; padding:12px 30px; border-radius:25px; font-size:15px; font-weight:600; text-decoration:none; }
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
        <a href="cart.php">🛒 Cart (<span id="cart-count">0</span>)</a>
        <?php if(isset($_SESSION['user_name'])): ?>
            <span style="color:white;">Hi, <?php echo $_SESSION['user_name']; ?>!</span>
            <a href="logout.php" class="btn-login">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-register">Register</a>
        <?php endif; ?>
    </div>
</nav>
<div class="cart-page">
    <h1 class="cart-title">🛒 My Cart</h1>
    <div id="cart-content"></div>
</div>
<footer class="footer">
    <h3>Smart<span style="color:#ffd700">Mart</span></h3>
    <p>📍 123 Main Street, Colombo, Sri Lanka</p>
    <p>📞 +94 11 234 5678</p>
    <p>📧 info@smartmart.lk</p>
    <div class="footer-bottom"><p>© 2026 SmartMart. All Rights Reserved.</p></div>
</footer>
<script>
function loadCart() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let content = document.getElementById('cart-content');
    document.getElementById('cart-count').textContent = cart.reduce((s,i)=>s+i.quantity,0);
    if(cart.length === 0) {
        content.innerHTML = '<div class="empty-cart"><p style="font-size:50px;">😕</p><p style="font-size:20px;font-weight:700;margin-bottom:15px;">Your cart is empty!</p><a href="products.php">Continue Shopping</a></div>';
        return;
    }
    let subtotal = 0;
    let rows = '';
    cart.forEach((item, index) => {
        let itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        rows += `<tr><td>${item.name}</td><td>LKR ${parseFloat(item.price).toFixed(2)}</td>
        <td><button class="qty-btn" onclick="changeQty(${index},-1)">-</button>
        <span class="qty-display">${item.quantity}</span>
        <button class="qty-btn" onclick="changeQty(${index},1)">+</button></td>
        <td>LKR ${itemTotal.toFixed(2)}</td>
        <td><button class="btn-remove" onclick="removeItem(${index})">🗑️ Remove</button></td></tr>`;
    });
    let tax = subtotal * 0.10;
    let total = subtotal + tax;
    content.innerHTML = `
    <div class="cart-table"><table>
        <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr></thead>
        <tbody>${rows}</tbody>
    </table></div>
    <div class="cart-summary">
        <h2>💰 Order Summary</h2>
        <div class="summary-row"><span>Subtotal</span><span>LKR ${subtotal.toFixed(2)}</span></div>
        <div class="summary-row"><span>Tax (10%)</span><span>LKR ${tax.toFixed(2)}</span></div>
        <div class="summary-row total"><span>Total</span><span>LKR ${total.toFixed(2)}</span></div>
        <button class="btn-checkout" onclick="checkout()">✅ Proceed to Checkout</button>
        <button class="btn-clear" onclick="clearCart()">🗑️ Clear Cart</button>
    </div>`;
}
function changeQty(index, change) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart[index].quantity += change;
    if(cart[index].quantity <= 0) cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}
function removeItem(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}
function clearCart() {
    if(confirm('Clear all items?')) { localStorage.removeItem('cart'); loadCart(); }
}
function checkout() {
    <?php if(isset($_SESSION['user_id'])): ?>
        window.location.href = 'checkout.php';
    <?php else: ?>
        alert('Please login to checkout!');
        window.location.href = 'login.php';
    <?php endif; ?>
}
loadCart();
</script>
</body>
</html>
