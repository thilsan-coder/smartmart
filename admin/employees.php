<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') { header('Location: ../login.php'); exit(); }
$success=''; $error='';
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_employee'])) {
    try {
        $pdo->prepare("INSERT INTO employees (name,email,phone,position,salary,joined_date) VALUES (?,?,?,?,?,?)")
            ->execute([$_POST['name'],$_POST['email'],$_POST['phone'],$_POST['position'],$_POST['salary'],$_POST['joined_date']]);
        $success='Employee added!';
    } catch(Exception $e) { $error='Something went wrong!'; }
}
if(isset($_GET['delete'])) {
    try { $pdo->prepare("DELETE FROM employees WHERE id=?")->execute([$_GET['delete']]); $success='Employee deleted!'; }
    catch(Exception $e) { $error='Cannot delete!'; }
}
$employees = $pdo->query("SELECT * FROM employees ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees - SmartMart Admin</title>
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
        .btn-add{background:#1a73e8;color:white;padding:10px 25px;border-radius:25px;font-size:15px;font-weight:600;cursor:pointer;border:none;}
        .add-form{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);margin-bottom:30px;display:none;}
        .add-form.show{display:block;}
        .add-form h2{font-size:20px;font-weight:700;color:#1a73e8;margin-bottom:20px;}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;}
        .form-group{display:flex;flex-direction:column;gap:6px;}
        .form-group label{font-size:14px;font-weight:600;color:#333;}
        .form-group input{padding:10px 15px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;outline:none;}
        .form-group input:focus{border-color:#1a73e8;}
        .btn-submit{background:#1a73e8;color:white;padding:12px 30px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;margin-top:15px;}
        .table-container{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);overflow-x:auto;}
        .table-container h2{font-size:20px;font-weight:700;color:#333;margin-bottom:20px;}
        table{width:100%;border-collapse:collapse;}
        thead{background:#1a73e8;color:white;}
        thead th{padding:12px 15px;text-align:left;font-size:14px;}
        tbody tr{border-bottom:1px solid #f0f0f0;}
        tbody tr:hover{background:#f8f9ff;}
        tbody td{padding:12px 15px;font-size:14px;color:#333;}
        .btn-delete{background:#ff4444;color:white;padding:6px 15px;border-radius:8px;font-size:13px;text-decoration:none;}
        .success-msg{background:#e0f7e9;color:#2e7d32;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;}
        .error-msg{background:#ffe0e0;color:#d32f2f;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;}
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
            <li><a href="employees.php" class="active">👥 Employees</a></li>
            <li><a href="reports.php">📈 Reports</a></li>
            <li><a href="../index.php">🏠 View Website</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="page-header">
            <h1>👥 Employees</h1>
            <button class="btn-add" onclick="document.getElementById('addForm').classList.toggle('show')">➕ Add Employee</button>
        </div>
        <?php if($success): ?><div class="success-msg">✅ <?php echo $success; ?></div><?php endif; ?>
        <?php if($error): ?><div class="error-msg">⚠️ <?php echo $error; ?></div><?php endif; ?>
        <div class="add-form" id="addForm">
            <h2>➕ Add New Employee</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group"><label>Full Name</label><input type="text" name="name" placeholder="Enter name" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="Enter email"></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" placeholder="Enter phone"></div>
                    <div class="form-group"><label>Position</label><input type="text" name="position" placeholder="e.g. Cashier, Manager"></div>
                    <div class="form-group"><label>Salary (LKR)</label><input type="number" name="salary" placeholder="0.00" step="0.01"></div>
                    <div class="form-group"><label>Joined Date</label><input type="date" name="joined_date"></div>
                </div>
                <button type="submit" name="add_employee" class="btn-submit">➕ Add Employee</button>
            </form>
        </div>
        <div class="table-container">
            <h2>👥 All Employees (<?php echo count($employees); ?>)</h2>
            <table>
                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Position</th><th>Salary</th><th>Joined</th><th>Action</th></tr></thead>
                <tbody>
                <?php if(count($employees) > 0): ?>
                    <?php foreach($employees as $i => $e): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><strong><?php echo $e['name']; ?></strong></td>
                        <td><?php echo $e['email'] ?? 'N/A'; ?></td>
                        <td><?php echo $e['phone'] ?? 'N/A'; ?></td>
                        <td><?php echo $e['position'] ?? 'N/A'; ?></td>
                        <td>LKR <?php echo number_format($e['salary'],2); ?></td>
                        <td><?php echo $e['joined_date'] ? date('d/m/Y',strtotime($e['joined_date'])) : 'N/A'; ?></td>
                        <td><a href="?delete=<?php echo $e['id']; ?>" class="btn-delete" onclick="return confirm('Delete?')">🗑️ Delete</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:#666;">No employees yet!</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
