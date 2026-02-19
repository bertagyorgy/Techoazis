<?php
// 1. Hibakeresés bekapcsolása (ha kész, kikommentelheted)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Munkamenet indítása (A COOKIE ellenőrzéshez és a SESSION-höz elengedhetetlen!)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// 3. Útvonalak beállítása (A régi logikát követve, ha a fájl a /core-ban van)
$current_root = dirname(__DIR__);
$isXampp = (strpos($current_root, 'htdocs') !== false);

if ($isXampp) {
    define('BASE_URL', $protocol . "://" . $host . '/Techoazis');
} else {
    define('BASE_URL', $protocol . "://" . $host);
}

// KRITIKUS: Visszaállítva a projekt gyökerére
define('ROOT_PATH', dirname(__DIR__));

// 4. Adatbázis behúzása (CSAK az útvonalak definiálása UTÁN)
// Feltételezve, hogy a db.php a projekt gyökerében lévő /app mappában van
require_once ROOT_PATH . '/app/db.php';

// 5. COOKIE token ellenőrzés (Most már van $conn az adatbázis fájlból)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    // Ellenőrizzük, hogy a $conn létezik-e (a db.php hozza létre)
    if (isset($conn)) {
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