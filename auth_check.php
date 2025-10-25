<?php
// Indítjuk az output buffert, hogy ne legyen idő előtti kimenet
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve és admin-e
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'A') {
    echo "<script>window.location.href='login.php';</script>";
    ob_end_flush();
    exit;
}

require 'db.php';
ob_end_flush();
?>
