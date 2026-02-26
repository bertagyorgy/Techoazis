<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';
// pages/test_tinify.php

// 1. Hibakeresés kényszerítése
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>--- Techoázis Tinify Diagnosztika (Éles szerver) ---</h2>";

// 2. Útvonalak meghatározása (a fájl helyzetéből indulva)
$baseDir = dirname(__DIR__); 

// 3. Konfiguráció és környezet betöltése
$configFile = $baseDir . '/core/config.php';
if (file_exists($configFile)) {
    // A session_start warningot itt elnyomjuk, mert csak tesztelünk
    @require_once $configFile;
    echo "✅ Config betöltve.<br>";
} else {
    die("❌ HIBA: A config.php nem található!");
}

require_once $baseDir . '/core/envreader.php';
loadEnv();

// 4. Az autoloader betöltése (Ez a legfontosabb a Tinify\Client miatt!)
$autoloadPath = $baseDir . '/core/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    echo "✅ Autoloader betöltve ($autoloadPath)<br>";
} else {
    echo "⚠️ Figyelem: Autoloader nem található!<br>";
}

// Az optimizer betöltése
require_once $baseDir . '/actions/image_optimizer.php';

echo "<hr>";

// 5. API Kulcs ellenőrzése
$key = getenv('TINIFY_API_KEY') ?: ($_ENV['TINIFY_API_KEY'] ?? null);
if ($key) {
    echo "✅ API Kulcs betöltve: <small>".substr($key, 0, 8)."...</small><br>";
} else {
    echo "❌ <span style='color:red'>HIBA: Az API kulcs hiányzik a .env-ből!</span><br>";
}

// 6. Végső teszt
if (class_exists('\Tinify\Tinify')) {
    echo "<h3 style='color:green'>🎉 SIKER: A Tinify osztály ELÉRHETŐ!</h3>";
    
    try {
        \Tinify\setKey($key);
        // Ez a pont teszteli a Tinify\Client-et és az internetkapcsolatot is
        \Tinify\validate();
        echo "✅ Tinify API kapcsolat: <b>OK</b> (Minden alosztály betöltve).<br>";
        
        $count = \Tinify\compressionCount();
        echo "ℹ️ Felhasznált tömörítések ebben a hónapban: " . ($count ?? 0);
        
    } catch (\Exception $e) {
        echo "❌ <span style='color:red'>API Hiba: " . $e->getMessage() . "</span><br>";
        echo "<i>(Ha 'Class Client not found' hibát látsz, az autoloader nem működik megfelelően.)</i>";
    }
} else {
    echo "<h3 style='color:red'>💀 HIBA: Az osztály továbbra sem tölthető be.</h3>";
}