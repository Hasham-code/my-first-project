<?php
require_once '../includes/auth_check.php';
require_role(['admin', 'staff']);
require_once '../config/db.php';

$msg = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM packages WHERE id = ?")->execute([$id]);
    $msg = "Package deleted successfully.";
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $duration = (int)$_POST['duration_days'];
    $price = (float)$_POST['price'];
    $discount = (int)$_POST['discount_percent'];
    $image_url = $_POST['image_url'];
    
    if (!empty($_POST['package_id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE packages SET title=?, description=?, duration_days=?, price=?, discount_percent=?, image_url=? WHERE id=?");
        $stmt->execute([$title, $desc, $duration, $price, $discount, $image_url, $_POST['package_id']]);
        $msg = "Package updated successfully.";
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO packages (title, description, duration_days, price, discount_percent, image_url, created_by_staff_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $duration, $price, $discount, $image_url, $_SESSION['user_id']]);
        $msg = "Package added successfully.";
    }
}

$packages = $pdo->query("SELECT * FROM packages ORDER BY created_at DESC")->fetchAll();

$edit_pkg = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_pkg = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Packages - GlobTrek</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .dashboard-container { max-width: 1200px; margin: 100px auto; padding: 20px; }
        .nav-tabs { display: flex; gap: 15px; margin-bottom: 30px; }
        .nav-tabs a { padding: 10px 20px; background: white; border-radius: 10px; text-decoration: none; color: var(--primary-blue); font-weight: bold; }
        .nav-tabs a.active { background: var(--primary-blue); color: white; }
        
        .form-card, .table-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #4b5563; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 5px; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: var(--primary-blue); }
        .action-links a { margin-right: 10px; text-decoration: none; font-weight: bold; }
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
        <h2 class="section-title">Manage Packages</h2>
        <div class="nav-tabs">
            <a href="index.php">Overview</a>
            <a href="manage_packages.php" class="active">Manage Packages</a>
            <a href="manage_bookings.php">Manage Bookings</a>
        </div>

        <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

        <div class="form-card">
            <h3><?= $edit_pkg ? 'Edit Package' : 'Add New Package' ?></h3><br>
            <form method="POST" action="manage_packages.php">
                <input type="hidden" name="package_id" value="<?= $edit_pkg ? $edit_pkg->id : '' ?>">
                
                <div class="form-group">
                    <label>Package Title</label>
                    <input type="text" name="title" required value="<?= $edit_pkg ? htmlspecialchars($edit_pkg->title) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Description (Features separated by commas)</label>
                    <textarea name="description" rows="3" required><?= $edit_pkg ? htmlspecialchars($edit_pkg->description) : '' ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Duration (Days)</label>
                        <input type="number" name="duration_days" required value="<?= $edit_pkg ? $edit_pkg->duration_days : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" required value="<?= $edit_pkg ? $edit_pkg->price : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Discount (%)</label>
                        <input type="number" name="discount_percent" value="<?= $edit_pkg ? $edit_pkg->discount_percent : '0' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Image Path (e.g., image/thailand.jpg)</label>
                    <input type="text" name="image_url" required value="<?= $edit_pkg ? htmlspecialchars($edit_pkg->image_url) : 'image/' ?>">
                </div>
                
                <button type="submit" class="btn"><?= $edit_pkg ? 'Update Package' : 'Save Package' ?></button>
                <?php if($edit_pkg): ?>
                    <a href="manage_packages.php" style="margin-left: 15px; color: red; text-decoration: none;">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <h3>Existing Packages</h3><br>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($packages as $p): ?>
                    <tr>
                        <td><?= $p->id ?></td>
                        <td><?= htmlspecialchars($p->title) ?></td>
                        <td><?= $p->duration_days ?> days</td>
                        <td>$<?= number_format($p->price, 2) ?></td>
                        <td><?= $p->discount_percent ?>%</td>
                        <td class="action-links">
                            <a href="manage_packages.php?edit=<?= $p->id ?>" style="color: var(--primary-blue);">Edit</a>
                            <a href="manage_packages.php?delete=<?= $p->id ?>" style="color: red;" onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($packages)): ?>
                    <tr><td colspan="6">No packages found. Add one above!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
