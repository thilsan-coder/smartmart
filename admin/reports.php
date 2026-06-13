<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') { header('Location: ../login.php'); exit(); }
try {
    $total_revenue = $pdo->query("SELECT SUM(total) FROM billing")->fetchColumn();
    $today_revenue = $pdo->query("SELECT SUM(total) FROM billing WHERE DATE(created_at)=CURDATE()")->fetchColumn();
    $month_revenue = $pdo->query("SELECT SUM(total) FROM billing WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $today_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $low_stock = $pdo->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC")->fetchAll(PDO::FETCH_ASSOC);
    $top_products = $pdo->query("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity*oi.price) as revenue FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $recent_bills = $pdo->query("SELECT * FROM billing ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $monthly_sales = $pdo->query("SELECT MONTH(created_at) as month, MONTHNAME(created_at) as month_name, COUNT(*) as total_bills, SUM(total) as total_revenue FROM billing WHERE YEAR(created_at)=YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $total_revenue=$today_revenue=$month_revenue=$total_orders=$today_orders=$total_products=0; $low_stock=$top_products=$recent_bills=$monthly_sales=[]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - SmartMart Admin</title>
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
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;}
        .page-header h1{font-size:26px;font-weight:800;color:#333;}
        .btn-print{background:#1a73e8;color:white;padding:10px 25px;border-radius:25px;font-size:15px;font-weight:600;cursor:pointer;border:none;}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:30px;}
        .stat-card{background:white;border-radius:15px;padding:20px;box-shadow:0 3px 15px rgba(0,0,0,0.08);text-align:center;}
        .stat-card .icon{font-size:35px;margin-bottom:10px;}
        .stat-card h3{font-size:22px;font-weight:800;color:#1a73e8;margin-bottom:5px;}
        .stat-card p{color:#666;font-size:13px;}
        .report-section{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);margin-bottom:25px;overflow-x:auto;}
        .report-section h2{font-size:20px;font-weight:700;color:#333;margin-bottom:20px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;}
        table{width:100%;border-collapse:collapse;}
        thead{background:#1a73e8;color:white;}
        thead th{padding:12px 15px;text-align:left;font-size:14px;}
        tbody tr{border-bottom:1px solid #f0f0f0;}
        tbody tr:hover{background:#f8f9ff;}
        tbody td{padding:12px 15px;font-size:14px;color:#333;}
        .stock-critical{color:#d32f2f;font-weight:700;}
        .stock-low{color:#e65100;font-weight:700;}
        .empty-msg{text-align:center;padding:30px;color:#666;}
        .chart-container{margin-top:15px;}
        .chart-bar-wrap{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
        .chart-label{width:80px;font-size:13px;color:#333;font-weight:600;}
        .chart-bar-bg{flex:1;background:#f0f0f0;border-radius:10px;height:25px;overflow:hidden;}
        .chart-bar{height:100%;background:linear-gradient(90deg,#1a73e8,#0d47a1);border-radius:10px;display:flex;align-items:center;padding-left:10px;color:white;font-size:12px;font-weight:600;}
        .chart-value{width:120px;font-size:13px;color:#1a73e8;font-weight:700;text-align:right;}
        @media print{.sidebar,.btn-print{display:none!important;}.main-content{margin-left:0!important;}}
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
            <li><a href="billing.php">💳 Billing</a></li>
            <li><a href="employees.php">👥 Employees</a></li>
            <li><a href="reports.php" class="active">📈 Reports</a></li>
            <li><a href="../index.php">🏠 View Website</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="page-header">
            <h1>📈 Reports</h1>
            <button class="btn-print" onclick="window.print()">🖨️ Print Report</button>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><div class="icon">💰</div><h3>LKR <?php echo number_format($total_revenue??0,0); ?></h3><p>Total Revenue</p></div>
            <div class="stat-card"><div class="icon">📅</div><h3>LKR <?php echo number_format($today_revenue??0,0); ?></h3><p>Today's Revenue</p></div>
            <div class="stat-card"><div class="icon">📆</div><h3>LKR <?php echo number_format($month_revenue??0,0); ?></h3><p>This Month</p></div>
            <div class="stat-card"><div class="icon">🛒</div><h3><?php echo $total_orders; ?></h3><p>Total Orders</p></div>
            <div class="stat-card"><div class="icon">📦</div><h3><?php echo $total_products; ?></h3><p>Total Products</p></div>
            <div class="stat-card"><div class="icon">🛍️</div><h3><?php echo $today_orders; ?></h3><p>Today's Orders</p></div>
        </div>
        <div class="report-section">
            <h2>📊 Monthly Sales - <?php echo date('Y'); ?></h2>
            <?php if(count($monthly_sales)>0): $max=max(array_column($monthly_sales,'total_revenue')); ?>
            <div class="chart-container">
                <?php foreach($monthly_sales as $m): $w=$max>0?($m['total_revenue']/$max)*100:0; ?>
                <div class="chart-bar-wrap">
                    <div class="chart-label"><?php echo substr($m['month_name'],0,3); ?></div>
                    <div class="chart-bar-bg"><div class="chart-bar" style="width:<?php echo $w; ?>%"><?php echo $m['total_bills']; ?> bills</div></div>
                    <div class="chart-value">LKR <?php echo number_format($m['total_revenue'],0); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?><p class="empty-msg">No sales data yet!</p><?php endif; ?>
        </div>
        <div class="report-section">
            <h2>🏆 Top Selling Products</h2>
            <table>
                <thead><tr><th>#</th><th>Product</th><th>Total Sold</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php if(count($top_products)>0): ?>
                    <?php foreach($top_products as $i=>$p): ?>
                    <tr><td><?php echo $i+1; ?></td><td><strong><?php echo $p['name']; ?></strong></td><td><?php echo $p['total_sold']; ?> units</td><td>LKR <?php echo number_format($p['revenue'],2); ?></td></tr>
                    <?php endforeach; ?>
                <?php else: ?><tr><td colspan="4" class="empty-msg">No sales data yet!</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="report-section">
            <h2>⚠️ Low Stock Alert</h2>
            <table>
                <thead><tr><th>#</th><th>Product</th><th>Stock</th><th>Status</th></tr></thead>
                <tbody>
                <?php if(count($low_stock)>0): ?>
                    <?php foreach($low_stock as $i=>$p): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><strong><?php echo $p['name']; ?></strong></td>
                        <td class="<?php echo $p['stock']<5?'stock-critical':'stock-low'; ?>"><?php echo $p['stock']; ?> units</td>
                        <td><?php if($p['stock']==0): ?><span style="background:#ffe0e0;color:#d32f2f;padding:4px 10px;border-radius:10px;font-size:12px;font-weight:700;">Out of Stock</span><?php elseif($p['stock']<5): ?><span style="background:#ffe0e0;color:#d32f2f;padding:4px 10px;border-radius:10px;font-size:12px;font-weight:700;">Critical</span><?php else: ?><span style="background:#fff3e0;color:#e65100;padding:4px 10px;border-radius:10px;font-size:12px;font-weight:700;">Low</span><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?><tr><td colspan="4" class="empty-msg">✅ All products have sufficient stock!</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="report-section">
            <h2>🧾 Recent Bills</h2>
            <table>
                <thead><tr><th>#</th><th>Invoice</th><th>Amount</th><th>Tax</th><th>Discount</th><th>Total</th><th>Payment</th><th>Date</th></tr></thead>
                <tbody>
                <?php if(count($recent_bills)>0): ?>
                    <?php foreach($recent_bills as $i=>$b): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><strong><?php echo $b['invoice_number']; ?></strong></td>
                        <td>LKR <?php echo number_format($b['amount'],2); ?></td>
                        <td>LKR <?php echo number_format($b['tax'],2); ?></td>
                        <td>LKR <?php echo number_format($b['discount'],2); ?></td>
                        <td><strong>LKR <?php echo number_format($b['total'],2); ?></strong></td>
                        <td><?php echo ucfirst($b['payment_method']); ?></td>
                        <td><?php echo date('d/m/Y H:i',strtotime($b['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?><tr><td colspan="8" class="empty-msg">No bills yet!</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
