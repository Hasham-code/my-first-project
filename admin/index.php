<?php
require_once '../includes/auth_check.php';
require_role('admin');
require_once '../config/db.php';

// Stats
$user_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()->count;
$pkg_count = $pdo->query("SELECT COUNT(*) as count FROM packages")->fetch()->count;
$booking_count = $pdo->query("SELECT COUNT(*) as count FROM bookings")->fetch()->count;
$sales = $pdo->query("SELECT SUM(total_price) as total FROM bookings WHERE status != 'cancelled'")->fetch()->total;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Admin Dashboard - GlobTrek</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .dashboard-container { max-width: 1200px; margin: 100px auto; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .stat-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { font-size: 2.5rem; color: var(--primary-blue); }
        .stat-card p { color: #64748b; font-size: 1.1rem; }
        .nav-tabs { display: flex; gap: 15px; margin-bottom: 30px; }
        .nav-tabs a { padding: 10px 20px; background: white; border-radius: 10px; text-decoration: none; color: var(--primary-blue); font-weight: bold; }
        .nav-tabs a.active { background: var(--primary-blue); color: white; }
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
        <h2 class="section-title">Admin Dashboard</h2>
        <div class="nav-tabs">
            <a href="index.php" class="active">Overview</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="reports.php">Reports</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>$<?= number_format($sales ?? 0, 2) ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card">
                <h3><?= $booking_count ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?= $user_count ?></h3>
                <p>Registered Users</p>
            </div>
            <div class="stat-card">
                <h3><?= $pkg_count ?></h3>
                <p>Active Packages</p>
            </div>
        </div>
    </div>
</body>
</html>
