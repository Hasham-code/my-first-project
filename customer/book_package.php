<?php
require_once '../includes/auth_check.php';
require_login(); // Allow anyone logged in to book
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../home.php");
    exit;
}

$package_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    die("Package not found.");
}

$price_per_person = $package->price;

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $travel_date = $_POST['travel_date'];
    $guests = (int)$_POST['guests'];
    $payment_method = $_POST['payment_method'];
    
    if (empty($travel_date) || $guests < 1) {
        $error = "Please provide valid travel date and guest count.";
    } else {
        $total_price = $guests * $price_per_person;
        
        try {
            $pdo->beginTransaction();
            
            // Insert Booking
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, package_id, travel_date, guests_count, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $package->id, $travel_date, $guests, $total_price]);
            $booking_id = $pdo->lastInsertId();
            
            // Insert Payment
            $stmt = $pdo->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->execute([$booking_id, $_SESSION['user_id'], $total_price, $payment_method]);
            
            $pdo->commit();
            
            $msg = "Booking successful! Your booking is pending confirmation from our staff.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Booking failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Book Package - GlobTrek</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .book-container { max-width: 900px; margin: 100px auto; padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        @media (max-width: 768px) { .book-container { grid-template-columns: 1fr; } }
        .pkg-summary { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); align-self: start;}
        .pkg-summary img { width: 100%; height: 250px; object-fit: cover; }
        .pkg-summary-content { padding: 20px; }
        .pkg-summary-content h2 { color: var(--primary-blue); font-size: 1.5rem; margin-bottom: 10px; }
        
        .book-form-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #4b5563; font-weight:bold;}
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 10px; outline: none; }
        .total-box { margin: 20px 0; padding: 15px; background: #f8fafc; border-left: 4px solid var(--accent-orange); font-size: 1.2rem; font-weight: bold; }
        .btn-book { width: 100%; text-align: center; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .msg.error { background: #fde8e8; color: #c81e1e; }
        .msg.success { background: #def7ec; color: #03543f; }
    </style>
</head>
<body style="background: var(--bg-light);">
    <header id="header" class="scrolled" style="background: rgba(255,255,255,0.95); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container">
            <a href="../home.php" class="nav_logo"><img src="../image/GlobeTrek.png" alt="GlobeTrek"></a>
            <nav><ul class="nav_links">
                <li><a href="../home.php" style="color:black;">Home</a></li>
                <?php if($_SESSION['role'] == 'customer'): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="../logout.php">Logout</a></li>
            </ul></nav>
        </div>
    </header>

    <div class="book-container">
        <!-- Package Details -->
        <div class="pkg-summary">
            <img src="../<?= htmlspecialchars($package->image_url) ?>" alt="Tour">
            <div class="pkg-summary-content">
                <h2><?= htmlspecialchars($package->title) ?></h2>
                <p><?= htmlspecialchars($package->description) ?></p>
                <hr style="border:none; border-top:1px solid #eee; margin:15px 0;">
                <p><strong>Duration:</strong> <?= $package->duration_days ?> Days</p>
                <p><strong>Price per person:</strong> $<?= number_format($price_per_person, 2) ?></p>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="book-form-card">
            <h2 style="color:var(--primary-blue); margin-bottom: 20px;">Complete Your Booking</h2>
            
            <?php if($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
            <?php if($msg): ?>
                <div class="msg success"><?= $msg ?></div>
                <div style="text-align:center;"><a href="dashboard.php" class="btn">View My Bookings</a></div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Travel Date</label>
                        <input type="date" name="travel_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Number of Guests</label>
                        <input type="number" id="guests" name="guests" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="credit_card">Credit Card (Simulated)</option>
                            <option value="paypal">PayPal (Simulated)</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <div class="total-box">
                        Total Amount: $<span id="total_price"><?= number_format($price_per_person, 2) ?></span>
                    </div>
                    
                    <button type="submit" class="btn btn-book">Confirm Booking & Pay</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const pricePerPerson = <?= $price_per_person ?>;
        const guestsInput = document.getElementById('guests');
        const totalSpan = document.getElementById('total_price');

        if(guestsInput) {
            guestsInput.addEventListener('input', function() {
                const guests = parseInt(this.value) || 1;
                const total = guests * pricePerPerson;
                totalSpan.textContent = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
        }
    </script>
</body>
</html>
