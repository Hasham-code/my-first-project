<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'customer')");
            if ($stmt->execute([$full_name, $email, $hash, $phone])) {
                $success = 'Registration successful! You can now <a href="login.php">login</a>.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - GlobTrek</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .auth-container { max-width: 400px; margin: 100px auto; padding: 40px; background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .auth-container h2 { text-align: center; color: var(--primary-blue); margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #4b5563; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 10px; outline: none; }
        .form-group input:focus { border-color: var(--primary-blue); }
        .auth-btn { width: 100%; text-align: center; margin-top: 10px; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .msg.error { background: #fde8e8; color: #c81e1e; }
        .msg.success { background: #def7ec; color: #03543f; }
        .switch-link { text-align: center; margin-top: 20px; font-size: 0.9rem; }
    </style>
</head>
<body style="background: var(--bg-light);">
    <header id="header" class="scrolled" style="background: rgba(255,255,255,0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container">
            <a href="index.php" class="nav_logo"><img src="image/GlobeTrek.png" alt="GlobeTrek"></a>
            <nav><ul class="nav_links"><li><a href="index.php" style="color:black;">Home</a></li></ul></nav>
        </div>
    </header>

    <div class="auth-container">
        <h2>Create Account</h2>
        <?php if($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="msg success"><?= $success ?></div><?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone">
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn auth-btn">Register</button>
        </form>
        <div class="switch-link">
            Already have an account? <a href="login.php" style="color: var(--accent-orange);">Login here</a>
        </div>
    </div>
</body>
</html>
