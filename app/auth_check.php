<?php

// Indítjuk az output buffert, hogy elkerüljük a "headers already sent" hibákat
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve és admin-e
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'A') {
    // JAVÍTÁS: Relatív út helyett BASE_URL-t használunk, és a routeren keresztül hívjuk a logint (.php nélkül)
    header("Location: " . BASE_URL . "/login");
    ob_end_clean(); // Kiszórjuk a buffert, mert úgyis átirányítunk
    exit();
}

// JAVÍTÁS: Az adatbázist a ROOT_PATH segítségével hívjuk be, így bárhonnan működik
require_once ROOT_PATH . '/app/db.php';

ob_end_flush();
?>