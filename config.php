<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Ellenőrizzük a környezetet a fájl elérési útja alapján
$isXampp = (strpos(__DIR__, 'htdocs') !== false);

if ($isXampp) {
    // XAMPP esetén hozzáadjuk a mappa nevet
    define('BASE_URL', $protocol . "://" . $host . '/Techoazis');
} else {
    // Docker esetén (vagy ha a gyökérbe mappelted) marad a tiszta host
    define('BASE_URL', $protocol . "://" . $host);
}

define('ROOT_PATH', __DIR__);

// Debug: Ha még mindig gond van, kommentezd ki az alábbi sort, hogy lásd, mit választott
// echo "Környezet: " . ($isXampp ? "XAMPP" : "Docker") . " | URL: " . BASE_URL;

ini_set('display_errors', 1);
error_reporting(E_ALL);