<?php
// 1. Config behívása a BASE_URL miatt
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. ELŐBB töröljük az adatbázisból, amíg megvan a session ID
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET remember_token=NULL, remember_expire=NULL WHERE user_id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
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
setcookie("remember_token", "", time() - 3600, "/");

session_destroy();

// 3. JAVÍTÁS: JavaScript helyett tiszta PHP átirányítás BASE_URL-el
header("Location: " . BASE_URL . "/index");
exit();