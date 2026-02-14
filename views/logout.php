<?php
// 1. Config behívása a BASE_URL miatt
require_once __DIR__ . '/../core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Session adatok teljes törlése
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// 3. JAVÍTÁS: JavaScript helyett tiszta PHP átirányítás BASE_URL-el
header("Location: " . BASE_URL . "/index");
exit();