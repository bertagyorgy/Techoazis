<?php
// Automatikusan felismeri a portot és a címet (pl. http://localhost:8080)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST']; 

// Dockerben nincs szükség a /Techoazis mappára az URL-ben, mert a gyökérbe (/) mutat
// De hogy XAMPP-on is jó maradjon, egy kis trükk:
if (strpos($_SERVER['REQUEST_URI'], '/Techoazis') !== false) {
    define('BASE_URL', $protocol . "://" . $host . '/Techoazis');
} else {
    define('BASE_URL', $protocol . "://" . $host);
}

define('ROOT_PATH', __DIR__);

ini_set('display_errors', 1);
error_reporting(E_ALL);