<?php
$servername = "localhost";
$username = "root";
$password = ""; // Alapértelmezett jelszó XAMPP esetén
$dbname = "techoazis_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>