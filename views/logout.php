<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Ha be van jelentkezve, töröljük az adatokat
if (isset($_SESSION['user_id'])) {
    $_SESSION = [];
    session_unset();
    session_destroy();
}

// Biztonságosabb header-es redirect
header("Location: ../index.php");
exit();
