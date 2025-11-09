<?php
$servername = "localhost";
$username = "root";
$password = ""; // Alapértelmezett jelszó XAMPP esetén
$dbname = "techoazis_db";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>