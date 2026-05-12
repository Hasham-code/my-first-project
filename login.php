<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password_hash)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['role'] = $user->role;
            $_SESSION['full_name'] = $user->full_name;

            // Redirect based on role
            if ($user->role === 'admin') {
                header("Location: admin/index.php");
            } elseif ($user->role === 'staff') {
                header("Location: staff/index.php");
            } else {
                header("Location: customer/dashboard.php");
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - GlobTrek</title>
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
        .switch-link { text-align: center; margin-top: 20px; font-size: 0.9rem; }
    </style>
</head>
<body style="background: var(--bg-light);">
    <header id="header" class="scrolled" style="background: rgba(255,255,255,0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container">
            <a href="home.php" class="nav_logo"><img src="image/GlobeTrek.png" alt="GlobeTrek"></a>
            <nav><ul class="nav_links"><li><a href="home.php" style="color:black;">Home</a></li></ul></nav>
        </div>
    </header>

    <div class="auth-container">
        <h2>Welcome Back</h2>
        <?php if($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn auth-btn">Login</button>
        </form>
        <div class="switch-link">
            Don't have an account? <a href="register.php" style="color: var(--accent-orange);">Register here</a>
        </div>
    </div>
</body>
</html>
