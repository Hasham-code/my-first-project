<?php
require_once '../includes/auth_check.php';
require_role('admin');
require_once '../config/db.php';

$msg = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        $msg = "User deleted.";
    } else {
        $msg = "Cannot delete yourself.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role'];
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $msg = "Email already exists.";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $role]);
        $msg = "User added successfully.";
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .dashboard-container { max-width: 1200px; margin: 100px auto; padding: 20px; }
        .nav-tabs { display: flex; gap: 15px; margin-bottom: 30px; }
        .nav-tabs a { padding: 10px 20px; background: white; border-radius: 10px; text-decoration: none; color: var(--primary-blue); font-weight: bold; }
        .nav-tabs a.active { background: var(--primary-blue); color: white; }
        
        .form-card, .table-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px;}
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #4b5563; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 5px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: var(--primary-blue); }
        .role-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-staff { background: #e0e7ff; color: #3730a3; }
        .role-customer { background: #dcfce7; color: #166534; }
        .msg { padding: 10px; background: #def7ec; color: #03543f; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body style="background: var(--bg-light);">
    <header id="header" class="scrolled" style="background: rgba(255,255,255,0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container">
            <a href="../home.php" class="nav_logo"><img src="../image/GlobeTrek.png" alt="GlobeTrek"></a>
            <nav><ul class="nav_links">
                <li><a href="../home.php" style="color:black;">Home</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul></nav>
        </div>
    </header>

    <div class="dashboard-container">
        <h2 class="section-title">Manage Users</h2>
        <div class="nav-tabs">
            <a href="index.php">Overview</a>
            <a href="manage_users.php" class="active">Manage Users</a>
            <a href="reports.php">Reports</a>
        </div>

        <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

        <div class="form-card">
            <h3>Add New User</h3><br>
            <form method="POST" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; align-items:end;">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Password</label><input type="text" name="password" required></div>
                <div class="form-group"><label>Role</label>
                    <select name="role">
                        <option value="customer">Customer</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="grid-column: span 2;">Add User</button>
            </form>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= $u->id ?></td>
                        <td><?= htmlspecialchars($u->full_name) ?></td>
                        <td><?= htmlspecialchars($u->email) ?></td>
                        <td><span class="role-badge role-<?= $u->role ?>"><?= $u->role ?></span></td>
                        <td><?= $u->created_at ?></td>
                        <td>
                            <?php if($u->id !== $_SESSION['user_id']): ?>
                                <a href="manage_users.php?delete=<?= $u->id ?>" style="color: red; font-weight:bold; text-decoration:none;" onclick="return confirm('Delete this user?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
