<?php
require_once '../includes/auth_check.php';
require_role('admin');
require_once '../config/db.php';

$query = "SELECT b.*, u.full_name as customer_name, p.title as package_name, pay.payment_method 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN packages p ON b.package_id = p.id 
          LEFT JOIN payments pay ON b.id = pay.booking_id
          WHERE b.status != 'cancelled'
          ORDER BY b.created_at DESC";
$bookings = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .dashboard-container { max-width: 1200px; margin: 100px auto; padding: 20px; }
        .nav-tabs { display: flex; gap: 15px; margin-bottom: 30px; }
        .nav-tabs a { padding: 10px 20px; background: white; border-radius: 10px; text-decoration: none; color: var(--primary-blue); font-weight: bold; }
        .nav-tabs a.active { background: var(--primary-blue); color: white; }
        
        .table-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: var(--primary-blue); }
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
        <h2 class="section-title">Sales Reports</h2>
        <div class="nav-tabs">
            <a href="index.php">Overview</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="reports.php" class="active">Reports</a>
        </div>

        <div class="table-card">
            <button onclick="window.print()" class="btn" style="margin-bottom: 20px;">Print Report</button>
            <table>
                <thead>
                    <tr><th>Booking ID</th><th>Customer</th><th>Package</th><th>Date</th><th>Amount</th><th>Method</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach($bookings as $b): 
                        $total += $b->total_price;
                    ?>
                    <tr>
                        <td>#<?= $b->id ?></td>
                        <td><?= htmlspecialchars($b->customer_name) ?></td>
                        <td><?= htmlspecialchars($b->package_name) ?></td>
                        <td><?= date('Y-m-d', strtotime($b->created_at)) ?></td>
                        <td>$<?= number_format($b->total_price, 2) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $b->payment_method)) ?></td>
                        <td><?= ucfirst($b->status) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8fafc; font-weight:bold; font-size:1.1rem;">
                        <td colspan="4" style="text-align:right;">Total Revenue:</td>
                        <td colspan="3">$<?= number_format($total, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
