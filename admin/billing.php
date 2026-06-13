<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') { header('Location: ../login.php'); exit(); }
$success = ''; $error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_billing'])) {
    $cart_items = json_decode($_POST['cart_data'], true);
    $payment_method = $_POST['payment_method'];
    $discount = floatval($_POST['discount'] ?? 0);
    $user_id = $_SESSION['user_id'];
    try {
        $subtotal = 0;
        foreach($cart_items as $item) { $subtotal += floatval($item['price']) * intval($item['quantity']); }
        $tax = $subtotal * 0.10;
        $total = $subtotal + $tax - $discount;
        $stmt = $pdo->prepare("INSERT INTO orders (user_id,total_amount,status,payment_status) VALUES (?,?,'completed','paid')");
        $stmt->execute([$user_id, $total]);
        $order_id = $pdo->lastInsertId();
        foreach($cart_items as $item) {
            $pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)")->execute([$order_id,$item['id'],$item['quantity'],$item['price']]);
            $pdo->prepare("UPDATE products SET stock=stock-? WHERE id=?")->execute([$item['quantity'],$item['id']]);
        }
        $invoice = 'SM-'.date('Ymd').'-'.$order_id;
        $pdo->prepare("INSERT INTO billing (order_id,invoice_number,amount,tax,discount,total,payment_method) VALUES (?,?,?,?,?,?,?)")->execute([$order_id,$invoice,$subtotal,$tax,$discount,$total,$payment_method]);
        $success = $invoice;
    } catch(Exception $e) { $error = 'Error: '.$e->getMessage(); }
}

if(isset($_GET['barcode'])) {
    header('Content-Type: application/json');
    $barcode = $_GET['barcode'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode=? OR id=?");
    $stmt->execute([$barcode,$barcode]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit();
}

$bills = $pdo->query("SELECT * FROM billing ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing - SmartMart Admin</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
        .admin-layout{display:flex;min-height:100vh;}
        .sidebar{width:250px;background:#1a73e8;color:white;padding:20px 0;position:fixed;height:100vh;overflow-y:auto;}
        .sidebar-logo{text-align:center;font-size:22px;font-weight:800;padding:15px 20px 30px;border-bottom:1px solid rgba(255,255,255,0.2);}
        .sidebar-logo span{color:#ffd700;}
        .sidebar-menu{list-style:none;padding:20px 0;}
        .sidebar-menu li a{display:block;padding:12px 25px;color:white;font-size:15px;font-weight:500;transition:0.3s;text-decoration:none;}
        .sidebar-menu li a:hover,.sidebar-menu li a.active{background:rgba(255,255,255,0.2);padding-left:35px;}
        .main-content{margin-left:250px;flex:1;background:#f0f4f8;padding:30px;}
        .page-header{margin-bottom:30px;}
        .page-header h1{font-size:26px;font-weight:800;color:#333;}
        .billing-grid{display:grid;grid-template-columns:1fr 350px;gap:20px;}
        .billing-section{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);}
        .billing-section h2{font-size:20px;font-weight:700;color:#1a73e8;margin-bottom:20px;}
        .barcode-search{display:flex;gap:10px;margin-bottom:20px;}
        .barcode-search input{flex:1;padding:12px 20px;border:2px solid #1a73e8;border-radius:10px;font-size:16px;outline:none;}
        .barcode-search button{padding:12px 25px;background:#1a73e8;color:white;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;}
        .billing-table{width:100%;border-collapse:collapse;margin-bottom:20px;}
        .billing-table thead{background:#1a73e8;color:white;}
        .billing-table thead th{padding:10px 15px;text-align:left;font-size:14px;}
        .billing-table tbody tr{border-bottom:1px solid #f0f0f0;}
        .billing-table tbody td{padding:10px 15px;font-size:14px;}
        .btn-remove-item{background:#ff4444;color:white;border:none;padding:4px 12px;border-radius:6px;cursor:pointer;font-size:13px;}
        .summary-box{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);height:fit-content;}
        .summary-box h2{font-size:20px;font-weight:700;color:#1a73e8;margin-bottom:20px;}
        .summary-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f0f0;font-size:15px;}
        .summary-row.total{font-size:20px;font-weight:800;color:#1a73e8;border-bottom:none;margin-top:10px;}
        .form-group{margin-bottom:15px;}
        .form-group label{display:block;font-size:14px;font-weight:600;color:#333;margin-bottom:6px;}
        .form-group input,.form-group select{width:100%;padding:10px 15px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;outline:none;}
        .btn-complete{width:100%;padding:15px;background:#2e7d32;color:white;border:none;border-radius:12px;font-size:17px;font-weight:700;cursor:pointer;margin-top:15px;}
        .btn-clear-bill{width:100%;padding:12px;background:#ff4444;color:white;border:none;border-radius:12px;font-size:15px;font-weight:600;cursor:pointer;margin-top:10px;}
        .invoice-box{background:#e0f7e9;border:2px solid #2e7d32;border-radius:15px;padding:25px;margin-bottom:20px;text-align:center;}
        .invoice-box h3{color:#2e7d32;font-size:22px;margin-bottom:10px;}
        .invoice-number{font-size:28px;font-weight:800;color:#1a73e8;}
        .recent-bills{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);margin-top:20px;overflow-x:auto;}
        .recent-bills h2{font-size:20px;font-weight:700;color:#333;margin-bottom:20px;}
        .error-msg{background:#ffe0e0;color:#d32f2f;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;}
        .empty-msg{text-align:center;padding:30px;color:#666;}
        .btn-print-invoice{background:#0d47a1;color:white;padding:6px 15px;border-radius:8px;font-size:13px;text-decoration:none;cursor:pointer;border:none;}

        /* PRINT INVOICE STYLES */
        .invoice-modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center;}
        .invoice-modal.show{display:flex;}
        .invoice-print-box{background:white;border-radius:15px;padding:40px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;}
        .invoice-header{text-align:center;margin-bottom:30px;border-bottom:2px solid #1a73e8;padding-bottom:20px;}
        .invoice-logo{font-size:32px;font-weight:800;color:#1a73e8;}
        .invoice-logo span{color:#ffd700;}
        .invoice-address{color:#666;font-size:13px;margin-top:5px;}
        .invoice-title{font-size:20px;font-weight:700;color:#333;margin:15px 0 5px;}
        .invoice-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;font-size:13px;}
        .invoice-meta div{color:#666;}
        .invoice-meta strong{color:#333;}
        .invoice-items table{width:100%;border-collapse:collapse;}
        .invoice-items thead{background:#1a73e8;color:white;}
        .invoice-items thead th{padding:8px 12px;text-align:left;font-size:13px;}
        .invoice-items tbody td{padding:8px 12px;font-size:13px;border-bottom:1px solid #f0f0f0;}
        .invoice-totals{margin-top:20px;border-top:2px solid #f0f0f0;padding-top:15px;}
        .invoice-total-row{display:flex;justify-content:space-between;padding:5px 0;font-size:14px;}
        .invoice-total-row.final{font-size:18px;font-weight:800;color:#1a73e8;border-top:2px solid #1a73e8;margin-top:10px;padding-top:10px;}
        .invoice-footer{text-align:center;margin-top:30px;color:#666;font-size:12px;border-top:1px solid #f0f0f0;padding-top:15px;}
        .invoice-actions{display:flex;gap:10px;margin-top:20px;}
        .btn-print-now{flex:1;padding:12px;background:#1a73e8;color:white;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;}
        .btn-close-modal{flex:1;padding:12px;background:#666;color:white;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;}

        @media print {
            .admin-layout > .sidebar, .main-content > .page-header, .billing-grid, .recent-bills, .invoice-actions { display:none!important; }
            .invoice-modal { position:static!important; background:none!important; display:block!important; }
            .invoice-print-box { border-radius:0; padding:20px; max-height:none; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Smart<span>Mart</span> Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="products.php">📦 Products</a></li>
            <li><a href="orders.php">🛒 Orders</a></li>
            <li><a href="billing.php" class="active">💳 Billing</a></li>
            <li><a href="employees.php">👥 Employees</a></li>
            <li><a href="reports.php">📈 Reports</a></li>
            <li><a href="../index.php">🏠 View Website</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="page-header"><h1>💳 Billing System</h1></div>

        <?php if($error): ?><div class="error-msg">⚠️ <?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?>
        <div class="invoice-box">
            <h3>✅ Bill Complete!</h3>
            <p>Invoice Number:</p>
            <div class="invoice-number"><?php echo $success; ?></div>
            <button class="btn-print-invoice" style="margin-top:15px;font-size:15px;padding:10px 25px;" onclick="showInvoice('<?php echo $success; ?>')">🖨️ Print Invoice</button>
        </div>
        <?php endif; ?>

        <div class="billing-grid">
            <div class="billing-section">
                <h2>🔍 Scan / Search Product</h2>
                <div class="barcode-search">
                    <input type="text" id="barcodeInput" placeholder="Enter barcode or product ID..." autofocus>
                    <button onclick="searchProduct()">Add ➕</button>
                </div>
                <table class="billing-table">
                    <thead><tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th>Remove</th></tr></thead>
                    <tbody id="billingItems"><tr><td colspan="6" class="empty-msg">No items added. Scan a product!</td></tr></tbody>
                </table>
            </div>

            <div class="summary-box">
                <h2>💰 Bill Summary</h2>
                <div class="summary-row"><span>Subtotal</span><span id="subtotal">LKR 0.00</span></div>
                <div class="summary-row"><span>Tax (10%)</span><span id="tax">LKR 0.00</span></div>
                <div class="summary-row"><span>Discount</span><span id="discount-display">LKR 0.00</span></div>
                <div class="summary-row total"><span>Total</span><span id="grand-total">LKR 0.00</span></div>
                <form method="POST" id="billingForm">
                    <input type="hidden" name="cart_data" id="cartData">
                    <div class="form-group" style="margin-top:20px;"><label>Discount (LKR)</label><input type="number" name="discount" id="discountInput" placeholder="0" min="0" onchange="updateSummary()"></div>
                    <div class="form-group"><label>Payment Method</label>
                        <select name="payment_method">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Card</option>
                            <option value="online">📱 Online</option>
                        </select>
                    </div>
                    <button type="button" class="btn-complete" onclick="completeBill()">✅ Complete Bill</button>
                    <button type="button" class="btn-clear-bill" onclick="clearBill()">🗑️ Clear Bill</button>
                </form>
            </div>
        </div>

        <div class="recent-bills">
            <h2>🧾 Recent Bills</h2>
            <table class="billing-table">
                <thead><tr><th>#</th><th>Invoice</th><th>Amount</th><th>Tax</th><th>Discount</th><th>Total</th><th>Payment</th><th>Date</th><th>Print</th></tr></thead>
                <tbody>
                <?php if(count($bills) > 0): ?>
                    <?php foreach($bills as $i => $bill): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><strong><?php echo $bill['invoice_number']; ?></strong></td>
                        <td>LKR <?php echo number_format($bill['amount'],2); ?></td>
                        <td>LKR <?php echo number_format($bill['tax'],2); ?></td>
                        <td>LKR <?php echo number_format($bill['discount'],2); ?></td>
                        <td><strong>LKR <?php echo number_format($bill['total'],2); ?></strong></td>
                        <td><?php echo ucfirst($bill['payment_method']); ?></td>
                        <td><?php echo date('d/m/Y H:i',strtotime($bill['created_at'])); ?></td>
                        <td><button class="btn-print-invoice" onclick="showInvoice('<?php echo $bill['invoice_number']; ?>', <?php echo $bill['amount']; ?>, <?php echo $bill['tax']; ?>, <?php echo $bill['discount']; ?>, <?php echo $bill['total']; ?>, '<?php echo $bill['payment_method']; ?>', '<?php echo $bill['created_at']; ?>')">🖨️ Print</button></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="empty-msg">No bills yet!</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- INVOICE PRINT MODAL -->
<div class="invoice-modal" id="invoiceModal">
    <div class="invoice-print-box" id="invoicePrintContent">
        <!-- Invoice content loads here -->
    </div>
</div>

<script>
let cart = [];

async function searchProduct() {
    let barcode = document.getElementById('barcodeInput').value.trim();
    if(!barcode) return;
    try {
        let response = await fetch(`billing.php?barcode=${barcode}`);
        let product = await response.json();
        if(product && product.id) {
            addToCart(product);
            document.getElementById('barcodeInput').value = '';
            document.getElementById('barcodeInput').focus();
        } else { alert('Product not found!'); }
    } catch(e) { alert('Error searching product!'); }
}

function addToCart(product) {
    let existing = cart.find(i => i.id === product.id);
    if(existing) { existing.quantity += 1; }
    else { cart.push({id:product.id, name:product.name, price:parseFloat(product.price), quantity:1}); }
    renderCart();
}

function renderCart() {
    let tbody = document.getElementById('billingItems');
    if(cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-msg">No items added. Scan a product!</td></tr>';
        updateSummary(); return;
    }
    tbody.innerHTML = cart.map((item,i) => `<tr>
        <td>${i+1}</td><td>${item.name}</td>
        <td>LKR ${item.price.toFixed(2)}</td>
        <td>
            <button class="btn-remove-item" onclick="changeQty(${i},-1)">-</button>
            <strong> ${item.quantity} </strong>
            <button class="btn-remove-item" style="background:#1a73e8;" onclick="changeQty(${i},1)">+</button>
        </td>
        <td>LKR ${(item.price*item.quantity).toFixed(2)}</td>
        <td><button class="btn-remove-item" onclick="removeItem(${i})">🗑️</button></td>
    </tr>`).join('');
    updateSummary();
}

function changeQty(i, c) { cart[i].quantity += c; if(cart[i].quantity <= 0) cart.splice(i,1); renderCart(); }
function removeItem(i) { cart.splice(i,1); renderCart(); }

function updateSummary() {
    let subtotal = cart.reduce((s,i) => s+(i.price*i.quantity), 0);
    let tax = subtotal * 0.10;
    let discount = parseFloat(document.getElementById('discountInput').value) || 0;
    let total = subtotal + tax - discount;
    document.getElementById('subtotal').textContent = 'LKR '+subtotal.toFixed(2);
    document.getElementById('tax').textContent = 'LKR '+tax.toFixed(2);
    document.getElementById('discount-display').textContent = 'LKR '+discount.toFixed(2);
    document.getElementById('grand-total').textContent = 'LKR '+total.toFixed(2);
}

function completeBill() {
    if(cart.length === 0) { alert('Please add products first!'); return; }
    if(confirm('Complete this bill?')) {
        document.getElementById('cartData').value = JSON.stringify(cart);
        let form = document.getElementById('billingForm');
        let h = document.createElement('input');
        h.type='hidden'; h.name='complete_billing'; h.value='1';
        form.appendChild(h);
        form.submit();
    }
}

function clearBill() {
    if(confirm('Clear all items?')) { cart = []; renderCart(); document.getElementById('discountInput').value = ''; }
}

function showInvoice(invoiceNo, amount, tax, discount, total, payment, date) {
    amount = amount || 0; tax = tax || 0; discount = discount || 0; total = total || 0;
    payment = payment || 'cash'; date = date || new Date().toLocaleString();

    let itemsHtml = '';
    if(cart.length > 0) {
        itemsHtml = cart.map((item,i) => `<tr>
            <td>${i+1}</td><td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>LKR ${item.price.toFixed(2)}</td>
            <td>LKR ${(item.price*item.quantity).toFixed(2)}</td>
        </tr>`).join('');
    } else {
        itemsHtml = '<tr><td colspan="5" style="text-align:center;color:#666;">Items not available for this invoice</td></tr>';
    }

    let content = `
    <div class="invoice-header">
        <div class="invoice-logo">Smart<span>Mart</span></div>
        <div class="invoice-address">📍 123 Main Street, Colombo, Sri Lanka<br>📞 +94 11 234 5678 | 📧 info@smartmart.lk</div>
        <div class="invoice-title">🧾 INVOICE</div>
    </div>
    <div class="invoice-meta">
        <div><strong>Invoice No:</strong><br>${invoiceNo}</div>
        <div><strong>Date:</strong><br>${date}</div>
        <div><strong>Payment:</strong><br>${payment.toUpperCase()}</div>
        <div><strong>Status:</strong><br>✅ PAID</div>
    </div>
    <div class="invoice-items">
        <table>
            <thead><tr><th>#</th><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>${itemsHtml}</tbody>
        </table>
    </div>
    <div class="invoice-totals">
        <div class="invoice-total-row"><span>Subtotal</span><span>LKR ${parseFloat(amount).toFixed(2)}</span></div>
        <div class="invoice-total-row"><span>Tax (10%)</span><span>LKR ${parseFloat(tax).toFixed(2)}</span></div>
        <div class="invoice-total-row"><span>Discount</span><span>- LKR ${parseFloat(discount).toFixed(2)}</span></div>
        <div class="invoice-total-row final"><span>TOTAL</span><span>LKR ${parseFloat(total).toFixed(2)}</span></div>
    </div>
    <div class="invoice-footer">
        <p>Thank you for shopping at SmartMart! 🛒</p>
        <p>Visit us again at www.smartmart.lk</p>
    </div>
    <div class="invoice-actions">
        <button class="btn-print-now" onclick="window.print()">🖨️ Print Invoice</button>
        <button class="btn-close-modal" onclick="document.getElementById('invoiceModal').classList.remove('show')">✖ Close</button>
    </div>`;

    document.getElementById('invoicePrintContent').innerHTML = content;
    document.getElementById('invoiceModal').classList.add('show');
}

document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') searchProduct();
});
</script>
</body>
</html>
