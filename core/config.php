<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Ellenőrizzük a környezetet
// Mivel a fájl a /core-ban van, a dirname(__DIR__) adja meg a projekt valódi gyökerét
$current_root = dirname(__DIR__);
$isXampp = (strpos($current_root, 'htdocs') !== false);

if ($isXampp) {
    // XAMPP esetén marad a Techoazis mappa elérése
    define('BASE_URL', $protocol . "://" . $host . '/Techoazis');
} else {
    // Docker esetén marad a tiszta host
    define('BASE_URL', $protocol . "://" . $host);
}

// KRITIKUS JAVÍTÁS: 
// Most, hogy a config.php a /core mappában van, 
// a ROOT_PATH-nak egy szinttel feljebb kell mutatnia!
define('ROOT_PATH', dirname(__DIR__));

// Debug (szükség esetén):
// echo "ROOT_PATH: " . ROOT_PATH;

ini_set('display_errors', 1);
error_reporting(E_ALL);