<?php
// core/config.php

// 1. Hibakeresés bekapcsolása (ha kész, kikommentelheted)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- ÚJ: GLOBÁLIS IDŐZÓNA BEÁLLÍTÁSA ---
// Ezután a date() és time() függvények mindig a magyar időt fogják használni.
date_default_timezone_set('Europe/Budapest');

// 2. Munkamenet indítása (Hibaelnyomással a cPanel jogosultsági hiba ellen)
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// 3. Útvonalak beállítása
$current_root = dirname(__DIR__);
$isXampp = (strpos($current_root, 'htdocs') !== false);

if ($isXampp) {
    define('BASE_URL', $protocol . "://" . $host . '/Techoazis');
} else {
    define('BASE_URL', $protocol . "://" . $host);
}

// KRITIKUS: Visszaállítva a projekt gyökerére
define('ROOT_PATH', dirname(__DIR__));

// 4. Adatbázis behúzása
require_once ROOT_PATH . '/app/db.php';

// 5. COOKIE token ellenőrzés
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    if (isset($conn)) {
        // Itt a NOW() a MySQL-t használja, ami szintén a szerveridejét nézi
        $stmt = $conn->prepare("SELECT user_id, username, user_role FROM users WHERE remember_token=? AND remember_expire > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['loggedin'] = true;
        }
    }
}