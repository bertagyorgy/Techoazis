<?php

// Indítjuk az output buffert, hogy elkerüljük a "headers already sent" hibákat
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/pages/profile");
    ob_end_clean(); // Kiszórjuk a buffert, mert úgyis átirányítunk
    exit();
}

// JAVÍTÁS: Az adatbázist a ROOT_PATH segítségével hívjuk be, így bárhonnan működik
require_once ROOT_PATH . '/app/db.php';

ob_end_flush();
?>