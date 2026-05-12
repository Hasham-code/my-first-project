<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
        die("Unauthorized access. You do not have permission to view this page.");
    }
}
?>
