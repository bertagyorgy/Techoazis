<?php
require_once __DIR__ . '/app/db.php';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Ellenőrizzük a környezetet a fájl elérési útja alapján
$isXampp = (strpos(__DIR__, 'htdocs') !== false);

if ($isXampp) {
    // XAMPP esetén hozzáadjuk a mappa nevet
    define('BASE_URL', $protocol . "://" . $host . '/Techoazis');
} else {
    // Docker esetén (vagy ha a gyökérbe mappelted) marad a tiszta host
    define('BASE_URL', $protocol . "://" . $host);
}

define('ROOT_PATH', __DIR__);

// COOKIE token ellenőrzés
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("SELECT user_id, username, user_role FROM users WHERE remember_token=? AND remember_expire > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Fontos: user_id-t használj, mert a lekérdezésben is az van!
        $_SESSION['user_id'] = $user['user_id']; 
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['user_role'];
        $_SESSION['loggedin'] = true;
    }
}
// Debug: Ha még mindig gond van, kommentezd ki az alábbi sort, hogy lásd, mit választott
// echo "Környezet: " . ($isXampp ? "XAMPP" : "Docker") . " | URL: " . BASE_URL;

//ini_set('display_errors', 1);
//error_reporting(E_ALL);