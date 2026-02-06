<?php
$servername = getenv('DB_HOST') ?: "localhost";
$username   = getenv('DB_USER') ?: "root";
$password   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ""; 
$dbname     = getenv('DB_NAME') ?: "techoazis_db";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Sikerelen csatlakozás: " . $conn->connect_error);
}
?>