<?php
require_once '../includes/auth_check.php';
require_role(['admin', 'staff']);
require_once '../config/db.php';

$msg = '';

if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?")->execute([$id]);
    $msg = "Booking confirmed successfully.";
}

if (isset($_GET['cancel'])) {
    $id = (int)$_GET['cancel'];
    $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?")->execute([$id]);
    $msg = "Booking cancelled.";
}

$query = "SELECT b.*, u.full_name, u.email, p.title as package_title 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN packages p ON b.package_id = p.id 
          ORDER BY b.created_at DESC";
$bookings = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Bookings - GlobTrek</title>
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
        .status { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status.pending { background: #fef3c7; color: #d97706; }
        .status.confirmed { background: #def7ec; color: #03543f; }
        .status.cancelled { background: #fde8e8; color: #c81e1e; }
        .action-links a { margin-right: 10px; text-decoration: none; font-weight: bold; font-size: 0.9rem;}
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
        <h2 class="section-title">Manage Bookings</h2>
        <div class="nav-tabs">
            <a href="index.php">Overview</a>
            <a href="manage_packages.php">Manage Packages</a>
            <a href="manage_bookings.php" class="active">Manage Bookings</a>
        </div>

        <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Travel Date</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $b): ?>
                    <tr>
                        <td>#<?= $b->id ?></td>
                        <td><?= htmlspecialchars($b->full_name) ?><br><small><?= htmlspecialchars($b->email) ?></small></td>
                        <td><?= htmlspecialchars($b->package_title) ?></td>
                        <td><?= $b->travel_date ?></td>
                        <td><?= $b->guests_count ?></td>
                        <td><span class="status <?= $b->status ?>"><?= ucfirst($b->status) ?></span></td>
                        <td class="action-links">
                            <?php if($b->status === 'pending'): ?>
                                <a href="manage_bookings.php?approve=<?= $b->id ?>" style="color: #059669;">Confirm</a>
                                <a href="manage_bookings.php?cancel=<?= $b->id ?>" style="color: #dc2626;" onclick="return confirm('Cancel this booking?');">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($bookings)): ?>
                    <tr><td colspan="7">No bookings found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
