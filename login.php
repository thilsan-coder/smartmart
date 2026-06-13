<?php
session_start();
include 'includes/db.php';
$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            if($user['role'] == 'admin') { header('Location: admin/dashboard.php'); }
            else { header('Location: index.php'); }
            exit();
        } else { $error = 'Invalid email or password!'; }
    } catch(Exception $e) { $error = 'Something went wrong!'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartMart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#1a73e8,#0d47a1); }
        .auth-box { background:white; padding:40px; border-radius:20px; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .auth-box h2 { text-align:center; color:#1a73e8; font-size:28px; font-weight:800; margin-bottom:8px; }
        .subtitle { text-align:center; color:#666; margin-bottom:30px; font-size:14px; }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; font-size:14px; font-weight:600; color:#333; margin-bottom:8px; }
        .form-group input { width:100%; padding:12px 15px; border:2px solid #e0e0e0; border-radius:10px; font-size:15px; transition:0.3s; outline:none; }
        .form-group input:focus { border-color:#1a73e8; }
        .btn-submit { width:100%; padding:14px; background:#1a73e8; color:white; border:none; border-radius:10px; font-size:16px; font-weight:700; cursor:pointer; transition:0.3s; }
        .btn-submit:hover { background:#0d47a1; }
        .error-msg { background:#ffe0e0; color:#d32f2f; padding:10px 15px; border-radius:8px; margin-bottom:20px; font-size:14px; text-align:center; }
        .auth-footer { text-align:center; margin-top:20px; font-size:14px; color:#666; }
        .auth-footer a { color:#1a73e8; font-weight:600; }
        .logo-text { text-align:center; font-size:24px; font-weight:800; color:#1a73e8; margin-bottom:20px; }
        .logo-text span { color:#ffd700; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <div class="logo-text">Smart<span>Mart</span></div>
        <h2>Welcome Back!</h2>
        <p class="subtitle">Login to your account</p>
        <?php if($error): ?><div class="error-msg">⚠️ <?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Email Address</label><input type="email" name="email" placeholder="Enter your email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Enter your password" required></div>
            <button type="submit" class="btn-submit">Login 🔐</button>
        </form>
        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p style="margin-top:8px;"><a href="index.php">← Back to Home</a></p>
        </div>
    </div>
</div>
</body>
</html>
