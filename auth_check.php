<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Ellenőrzi, hogy a felhasználó be van-e lépve ÉS admin-e
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'A') {
    header("Location: login.php"); // Ha nem, irányítsd a login oldalra
    exit;
}
require 'db.php';
?>