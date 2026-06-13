<?php
session_start();
include 'includes/db.php';
if(!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SmartMart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .page-title { font-size:32px; font-weight:800; color:#1a73e8; padding:40px 40px 0; }
        .checkout-page { max-width:1100px; margin:0 auto; padding:40px; display:grid; grid-template-columns:1fr 400px; gap:30px; }
        @media(max-width:768px) { .checkout-page { grid-template-columns:1fr; } }
        .checkout-section { background:white; border-radius:15px; padding:25px; box-shadow:0 3px 15px rgba(0,0,0,0.08); margin-bottom:20px; }
        .checkout-section h2 { font-size:20px; font-weight:700; color:#1a73e8; margin-bottom:20px; padding-bottom:10px; border-bottom:2px solid #f0f0f0; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; font-size:14px; font-weight:600; color:#333; margin-bottom:6px; }
        .form-group input, .form-group textarea { width:100%; padding:12px 15px; border:2px solid #e0e0e0; border-radius:10px; font-size:14px; outline:none; transition:0.3s; font-family:inherit; }
        .form-group input:focus, .form-group textarea:focus { border-color:#1a73e8; }
        .form-group textarea { resize:none; height:80px; }
        .order-summary { background:white; border-radius:15px; padding:25px; box-shadow:0 3px 15px rgba(0,0,0,0.08); height:fit-content; position:sticky; top:20px; }
        .order-summary h2 { font-size:20px; font-weight:700; color:#1a73e8; margin-bottom:20px; padding-bottom:10px; border-bottom:2px solid #f0f0f0; }
        .order-item { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0f0f0; font-size:14px; }
        .summary-row { display:flex; justify-content:space-between; padding:10px 0; font-size:15px; border-bottom:1px solid #f0f0f0; }
        .summary-row.total { font-size:20px; font-weight:800; color:#1a73e8; border-bottom:none; margin-top:10px; }
        .btn-place-order { width:100%; padding:15px; background:#2e7d32; color:white; border:none; border-radius:12px; font-size:17px; font-weight:700; cursor:pointer; margin-top:20px; transition:0.3s; }
        .btn-place-order:hover { background:#1b5e20; }
        .payment-methods { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:10px; }
        .payment-option { border:2px solid #e0e0e0; border-radius:10px; padding:12px; text-align:center; cursor:pointer; transition:0.3s; font-size:13px; font-weight:600; }
        .payment-option:hover, .payment-option.selected { border-color:#1a73e8; background:#e8f0fe; color:#1a73e8; }
        .payment-option .pay-icon { font-size:24px; display:block; margin-bottom:5px; }
        .success-box { text-align:center; padding:60px 40px; max-width:600px; margin:0 auto; }
        .success-icon { font-size:80px; margin-bottom:20px; }
        .success-box h2 { font-size:32px; font-weight:800; color:#2e7d32; margin-bottom:15px; }
        .invoice-display { background:#e0f7e9; border:2px solid #2e7d32; border-radius:15px; padding:20px; margin:20px 0; }
        .invoice-display h3 { color:#2e7d32; font-size:18px; margin-bottom:8px; }
        .invoice-number { font-size:26px; font-weight:800; color:#1a73e8; }
        .btn-continue { display:inline-block; background:#1a73e8; color:white; padding:14px 35px; border-radius:25px; font-size:16px; font-weight:700; text-decoration:none; margin-top:20px; }
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
        <span style="color:white;">Hi, <?php echo $_SESSION['user_name']; ?>!</span>
        <a href="logout.php" class="btn-login">Logout</a>
    </div>
</nav>
<div id="checkout-content"></div>
<footer class="footer">
    <h3>Smart<span style="color:#ffd700">Mart</span></h3>
    <p>📍 123 Main Street, Colombo, Sri Lanka</p>
    <p>📞 +94 11 234 5678</p>
    <p>📧 info@smartmart.lk</p>
    <div class="footer-bottom"><p>© 2026 SmartMart. All Rights Reserved.</p></div>
</footer>
<script>
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let selectedPayment = 'cash';

function loadCheckout() {
    let content = document.getElementById('checkout-content');
    if(cart.length === 0) {
        content.innerHTML = '<div style="text-align:center;padding:60px;"><p style="font-size:50px;">😕</p><p style="font-size:20px;">Your cart is empty!</p><a href="products.php" style="color:#1a73e8;">Continue Shopping</a></div>';
        return;
    }
    let subtotal = cart.reduce((s,i) => s+(i.price*i.quantity), 0);
    let tax = subtotal * 0.10;
    let total = subtotal + tax;
    let itemsHtml = cart.map(i => `<div class="order-item"><span>${i.name} x${i.quantity}</span><span>LKR ${(i.price*i.quantity).toFixed(2)}</span></div>`).join('');
    content.innerHTML = `
    <h1 class="page-title">✅ Checkout</h1>
    <div class="checkout-page">
        <div>
            <div class="checkout-section">
                <h2>👤 Delivery Information</h2>
                <div class="form-group"><label>Full Name</label><input type="text" id="del-name" value="<?php echo htmlspecialchars($user['name']); ?>"></div>
                <div class="form-group"><label>Phone</label><input type="text" id="del-phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"></div>
                <div class="form-group"><label>Email</label><input type="email" id="del-email" value="<?php echo htmlspecialchars($user['email']); ?>"></div>
                <div class="form-group"><label>Delivery Address</label><textarea id="del-address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea></div>
            </div>
            <div class="checkout-section">
                <h2>💳 Payment Method</h2>
                <div class="payment-methods">
                    <div class="payment-option selected" onclick="selectPayment(this,'cash')"><span class="pay-icon">💵</span>Cash</div>
                    <div class="payment-option" onclick="selectPayment(this,'card')"><span class="pay-icon">💳</span>Card</div>
                    <div class="payment-option" onclick="selectPayment(this,'online')"><span class="pay-icon">📱</span>Online</div>
                </div>
            </div>
        </div>
        <div class="order-summary">
            <h2>🛒 Order Summary</h2>
            ${itemsHtml}
            <div class="summary-row"><span>Subtotal</span><span>LKR ${subtotal.toFixed(2)}</span></div>
            <div class="summary-row"><span>Tax (10%)</span><span>LKR ${tax.toFixed(2)}</span></div>
            <div class="summary-row total"><span>Total</span><span>LKR ${total.toFixed(2)}</span></div>
            <button class="btn-place-order" onclick="placeOrder()">✅ Place Order</button>
        </div>
    </div>`;
}

function selectPayment(el, method) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    selectedPayment = method;
}

async function placeOrder() {
    let name = document.getElementById('del-name').value;
    let phone = document.getElementById('del-phone').value;
    let address = document.getElementById('del-address').value;
    if(!name || !phone || !address) { alert('Please fill all delivery details!'); return; }
    if(confirm('Place order now?')) {
        let response = await fetch('php/place_order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({cart: cart, payment: selectedPayment, name, phone, address})
        });
        let result = await response.json();
        if(result.success) {
            localStorage.removeItem('cart');
            document.getElementById('checkout-content').innerHTML = `
            <div class="success-box">
                <div class="success-icon">🎉</div>
                <h2>Order Placed Successfully!</h2>
                <p>Thank you for shopping with SmartMart!</p>
                <div class="invoice-display">
                    <h3>Your Invoice Number:</h3>
                    <div class="invoice-number">${result.invoice}</div>
                </div>
                <p>We will deliver your order soon! 🚚</p>
                <a href="index.php" class="btn-continue">Continue Shopping 🛒</a>
            </div>`;
        } else { alert('Something went wrong! Please try again.'); }
    }
}
loadCheckout();
</script>
</body>
</html>
