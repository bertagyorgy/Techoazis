<?php
session_start();
include 'db.php';

// Ha a felhasználó be van jelentkezve
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $logout_date = date('Y-m-d H:i:s');
}

// Minden session adat törlése
$_SESSION = [];
session_unset();
session_destroy();

// Visszairányítás a kezdőlapra vagy login oldalra
header("Location: login.php");
exit();
?>
