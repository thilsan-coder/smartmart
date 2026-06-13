<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') { header('Location: ../login.php'); exit(); }
$success = ''; $error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name=$_POST['name']; $category_id=$_POST['category_id'];
    $description=$_POST['description']; $price=$_POST['price'];
    $stock=$_POST['stock']; $barcode=$_POST['barcode']; $image='';
    if($_FILES['image']['name'] != '') {
        $image = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../images/'.$image);
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name,category_id,description,price,stock,barcode,image) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$name,$category_id,$description,$price,$stock,$barcode,$image]);
        $success = 'Product added successfully!';
    } catch(Exception $e) { $error = 'Something went wrong!'; }
}
if(isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$_GET['delete']]);
        $success = 'Product deleted!';
    } catch(Exception $e) { $error = 'Cannot delete!'; }
}
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - SmartMart Admin</title>
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
        .btn-add{background:#1a73e8;color:white;padding:10px 25px;border-radius:25px;font-size:15px;font-weight:600;cursor:pointer;border:none;transition:0.3s;}
        .btn-add:hover{background:#0d47a1;}
        .add-form{background:white;border-radius:15px;padding:25px;box-shadow:0 3px 15px rgba(0,0,0,0.08);margin-bottom:30px;display:none;}
        .add-form.show{display:block;}
        .add-form h2{font-size:20px;font-weight:700;color:#1a73e8;margin-bottom:20px;}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;}
        .form-group{display:flex;flex-direction:column;gap:6px;}
        .form-group label{font-size:14px;font-weight:600;color:#333;}
        .form-group input,.form-group select,.form-group textarea{padding:10px 15px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;outline:none;transition:0.3s;font-family:inherit;}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#1a73e8;}
        .form-group textarea{resize:none;height:80px;}
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
        .stock-low{color:#d32f2f;font-weight:700;}
        .stock-ok{color:#2e7d32;font-weight:700;}
    </style>
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Smart<span>Mart</span> Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="products.php" class="active">📦 Products</a></li>
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
            <h1>📦 Products</h1>
            <button class="btn-add" onclick="document.getElementById('addForm').classList.toggle('show')">➕ Add Product</button>
        </div>
        <?php if($success): ?><div class="success-msg">✅ <?php echo $success; ?></div><?php endif; ?>
        <?php if($error): ?><div class="error-msg">⚠️ <?php echo $error; ?></div><?php endif; ?>
        <div class="add-form" id="addForm">
            <h2>➕ Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group"><label>Product Name</label><input type="text" name="name" placeholder="Enter product name" required></div>
                    <div class="form-group"><label>Category</label>
                        <select name="category_id" required><option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Price (LKR)</label><input type="number" name="price" placeholder="0.00" step="0.01" required></div>
                    <div class="form-group"><label>Stock</label><input type="number" name="stock" placeholder="0" required></div>
                    <div class="form-group"><label>Barcode</label><input type="text" name="barcode" placeholder="Enter barcode number"></div>
                    <div class="form-group"><label>Product Image</label><input type="file" name="image" accept="image/*"></div>
                    <div class="form-group" style="grid-column:1/-1;"><label>Description</label><textarea name="description" placeholder="Enter product description"></textarea></div>
                </div>
                <button type="submit" name="add_product" class="btn-submit">➕ Add Product</button>
            </form>
        </div>
        <div class="table-container">
            <h2>📦 All Products (<?php echo count($products); ?>)</h2>
            <table>
                <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Barcode</th><th>Action</th></tr></thead>
                <tbody>
                <?php if(count($products) > 0): ?>
                    <?php foreach($products as $i => $p): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><img src="<?php echo $p['image'] ? '../images/'.$p['image'] : 'https://via.placeholder.com/50x50?text=No+Img'; ?>" width="50" height="50" style="border-radius:8px;object-fit:cover;"></td>
                        <td><?php echo $p['name']; ?></td>
                        <td><?php echo $p['category_name'] ?? 'N/A'; ?></td>
                        <td>LKR <?php echo number_format($p['price'],2); ?></td>
                        <td class="<?php echo $p['stock'] < 10 ? 'stock-low' : 'stock-ok'; ?>"><?php echo $p['stock']; ?></td>
                        <td><?php echo $p['barcode'] ?? 'N/A'; ?></td>
                        <td><a href="?delete=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('Delete?')">🗑️ Delete</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:#666;">No products! Click "Add Product".</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
