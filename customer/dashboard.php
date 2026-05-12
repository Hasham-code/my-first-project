<?php
require_once '../includes/auth_check.php';
require_role('customer');
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Handle Booking Cancellation
$msg = '';
if (isset($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    // Ensure the booking belongs to this user and is pending
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$booking_id, $user_id]);
    $msg = "Booking has been cancelled.";
}

// Fetch user bookings
$stmt = $pdo->prepare("SELECT b.*, p.title as package_title, p.image_url 
                       FROM bookings b 
                       JOIN packages p ON b.package_id = p.id 
                       WHERE b.user_id = ? 
                       ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>My Dashboard - GlobTrek</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .dashboard-container { max-width: 1000px; margin: 100px auto; padding: 20px; }
        .greeting { color: var(--primary-blue); margin-bottom: 30px; font-size: 2rem; }
        .booking-card { display: flex; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .booking-img { width: 200px; object-fit: cover; }
        .booking-details { padding: 20px; flex: 1; }
        .booking-details h3 { color: var(--primary-blue); margin-bottom: 10px; }
        .meta-info { margin-bottom: 10px; color: #64748b; font-size: 0.95rem; }
        .status { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .status.pending { background: #fef3c7; color: #d97706; }
        .status.confirmed { background: #def7ec; color: #03543f; }
        .status.cancelled { background: #fde8e8; color: #c81e1e; }
        .cancel-btn { margin-top: 15px; display: inline-block; color: #dc2626; text-decoration: none; font-weight: bold; font-size: 0.9rem; border: 1px solid #dc2626; padding: 5px 15px; border-radius: 5px;}
        .cancel-btn:hover { background: #fef2f2; }
        .msg { padding: 10px; background: #def7ec; color: #03543f; border-radius: 5px; margin-bottom: 15px; }
        
        @media (max-width: 600px) {
            .booking-card { flex-direction: column; }
            .booking-img { width: 100%; height: 200px; }
        }
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
        <h2 class="greeting">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>
        
        <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

        <h3 style="margin-bottom:20px;">My Bookings</h3>
        
        <?php if(empty($bookings)): ?>
            <p>You have no bookings yet. <a href="../home.php#packages" style="color:var(--accent-orange);">Browse our packages!</a></p>
        <?php else: ?>
            <?php foreach($bookings as $b): ?>
                <div class="booking-card">
                    <img src="../<?= htmlspecialchars($b->image_url) ?>" class="booking-img" alt="Tour Image">
                    <div class="booking-details">
                        <h3><?= htmlspecialchars($b->package_title) ?></h3>
                        <div class="meta-info">
                            <strong>Travel Date:</strong> <?= $b->travel_date ?><br>
                            <strong>Guests:</strong> <?= $b->guests_count ?><br>
                            <strong>Total Price:</strong> $<?= number_format($b->total_price, 2) ?>
                        </div>
                        <span class="status <?= $b->status ?>">Status: <?= ucfirst($b->status) ?></span>
                        
                        <?php if($b->status === 'pending'): ?>
                            <br><a href="dashboard.php?cancel=<?= $b->id ?>" class="cancel-btn" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel Booking</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
