<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') { header('Location: ../login.php'); exit(); }
if(isset($_GET['status']) && isset($_GET['id'])) {
    $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$_GET['status'],$_GET['id']]);
}
$orders = $pdo->query("SELECT o.*, u.name as customer_name, u.email, u.phone FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders - SmartMart Admin</title>
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
        .table-container{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);overflow-x:auto;}
        .table-container h2{font-size:20px;font-weight:700;color:#333;margin-bottom:20px;}
        table{width:100%;border-collapse:collapse;}
        thead{background:#1a73e8;color:white;}
        thead th{padding:12px 15px;text-align:left;font-size:14px;}
        tbody tr{border-bottom:1px solid #f0f0f0;transition:0.2s;}
        tbody tr:hover{background:#f8f9ff;}
        tbody td{padding:12px 15px;font-size:14px;color:#333;}
        .status-badge{padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;}
        .status-pending{background:#fff3e0;color:#e65100;}
        .status-processing{background:#e3f2fd;color:#1565c0;}
        .status-completed{background:#e0f7e9;color:#2e7d32;}
        .status-cancelled{background:#ffe0e0;color:#d32f2f;}
        .payment-paid{background:#e0f7e9;color:#2e7d32;padding:4px 10px;border-radius:10px;font-size:12px;font-weight:700;}
        .payment-unpaid{background:#ffe0e0;color:#d32f2f;padding:4px 10px;border-radius:10px;font-size:12px;font-weight:700;}
        .action-links a{display:inline-block;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;margin-right:5px;}
        .btn-complete{background:#e0f7e9;color:#2e7d32;}
        .btn-cancel{background:#ffe0e0;color:#d32f2f;}
        .empty-msg{text-align:center;padding:40px;color:#666;}
    </style>
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Smart<span>Mart</span> Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="products.php">📦 Products</a></li>
            <li><a href="orders.php" class="active">🛒 Orders</a></li>
            <li><a href="billing.php">💳 Billing</a></li>
            <li><a href="employees.php">👥 Employees</a></li>
            <li><a href="reports.php">📈 Reports</a></li>
            <li><a href="../index.php">🏠 View Website</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="page-header"><h1>🛒 Orders</h1></div>
        <div class="table-container">
            <h2>All Orders (<?php echo count($orders); ?>)</h2>
            <table>
                <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Total</th><th>Status</th><th>Payment</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php if(count($orders) > 0): ?>
                    <?php foreach($orders as $i => $o): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><strong><?php echo $o['customer_name'] ?? 'Walk-in'; ?></strong><br><small style="color:#666;"><?php echo $o['email'] ?? ''; ?></small></td>
                        <td><?php echo $o['phone'] ?? 'N/A'; ?></td>
                        <td><strong>LKR <?php echo number_format($o['total_amount'],2); ?></strong></td>
                        <td><span class="status-badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                        <td><span class="payment-<?php echo $o['payment_status']; ?>"><?php echo ucfirst($o['payment_status']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i',strtotime($o['created_at'])); ?></td>
                        <td class="action-links">
                            <?php if($o['status'] != 'completed'): ?><a href="?id=<?php echo $o['id']; ?>&status=completed" class="btn-complete">✅ Complete</a><?php endif; ?>
                            <?php if($o['status'] != 'cancelled'): ?><a href="?id=<?php echo $o['id']; ?>&status=cancelled" class="btn-cancel" onclick="return confirm('Cancel?')">❌ Cancel</a><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="empty-msg">No orders yet!</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
