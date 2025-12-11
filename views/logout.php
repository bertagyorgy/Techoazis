<?php
session_start();

// Ha be van jelentkezve, töröljük az adatokat
if (isset($_SESSION['user_id'])) {
    $_SESSION = [];
    session_unset();
    session_destroy();
}

// Biztonságosabb header-es redirect
echo "<script>window.location.href='../index.php';</script>";
exit();
