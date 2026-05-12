<?php
require_once '../includes/auth_check.php';
require_role(['admin', 'staff']);
require_once '../config/db.php';

// Fetch quick stats
$stmt = $pdo->query("SELECT COUNT(*) as count FROM packages");
$pkg_count = $stmt->fetch()->count;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$pending_bookings = $stmt->fetch()->count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Staff Dashboard - GlobTrek</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .dashboard-container { max-width: 1200px; margin: 100px auto; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
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
                <li><a href="../logout.php">Logout (<?= htmlspecialchars($_SESSION['full_name']) ?>)</a></li>
            </ul></nav>
        </div>
    </header>

    <div class="dashboard-container">
        <h2 class="section-title">Staff Dashboard</h2>
        <div class="nav-tabs">
            <a href="index.php" class="active">Overview</a>
            <a href="manage_packages.php">Manage Packages</a>
            <a href="manage_bookings.php">Manage Bookings</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $pkg_count ?></h3>
                <p>Total Packages</p>
            </div>
            <div class="stat-card">
                <h3><?= $pending_bookings ?></h3>
                <p>Pending Bookings</p>
            </div>
        </div>
    </div>
</body>
</html>
