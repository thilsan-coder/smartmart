<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') { header('Location: ../login.php'); exit(); }
try {
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total) FROM billing")->fetchColumn();
} catch(Exception $e) { $total_products=$total_orders=$total_users=$total_revenue=0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartMart Admin</title>
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
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;}
        .stat-card{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);display:flex;align-items:center;gap:15px;}
        .stat-icon{font-size:40px;}
        .stat-info h3{font-size:28px;font-weight:800;color:#1a73e8;}
        .stat-info p{color:#666;font-size:14px;}
        .quick-actions{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);}
        .quick-actions h2{font-size:18px;font-weight:700;color:#333;margin-bottom:20px;}
        .actions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;}
        .action-btn{background:#1a73e8;color:white;padding:15px;border-radius:12px;text-align:center;text-decoration:none;font-weight:600;font-size:14px;transition:0.3s;}
        .action-btn:hover{background:#0d47a1;transform:translateY(-2px);}
        .btn-icon{font-size:24px;display:block;margin-bottom:8px;}
    </style>
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Smart<span>Mart</span> Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="products.php">📦 Products</a></li>
            <li><a href="orders.php">🛒 Orders</a></li>
            <li><a href="billing.php">💳 Billing</a></li>
            <li><a href="employees.php">👥 Employees</a></li>
            <li><a href="reports.php">📈 Reports</a></li>
            <li><a href="../index.php">🏠 View Website</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="page-header">
            <h1>📊 Dashboard</h1>
            <span style="color:#666;">👤 <?php echo $_SESSION['user_name']; ?></span>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-info"><h3><?php echo $total_products; ?></h3><p>Total Products</p></div></div>
            <div class="stat-card"><div class="stat-icon">🛒</div><div class="stat-info"><h3><?php echo $total_orders; ?></h3><p>Total Orders</p></div></div>
            <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-info"><h3><?php echo $total_users; ?></h3><p>Total Users</p></div></div>
            <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-info"><h3>LKR <?php echo number_format($total_revenue??0,0); ?></h3><p>Total Revenue</p></div></div>
        </div>
        <div class="quick-actions">
            <h2>⚡ Quick Actions</h2>
            <div class="actions-grid">
                <a href="products.php" class="action-btn"><span class="btn-icon">📦</span>Manage Products</a>
                <a href="products.php?action=add" class="action-btn"><span class="btn-icon">➕</span>Add Product</a>
                <a href="orders.php" class="action-btn"><span class="btn-icon">🛒</span>View Orders</a>
                <a href="billing.php" class="action-btn"><span class="btn-icon">💳</span>Billing</a>
                <a href="employees.php" class="action-btn"><span class="btn-icon">👥</span>Employees</a>
                <a href="reports.php" class="action-btn"><span class="btn-icon">📈</span>Reports</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
