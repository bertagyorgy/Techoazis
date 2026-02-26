<?php
// actions/image_optimizer.php

/**
 * Kép optimalizálása Tinify API-val
 * @param string $filePath A kép abszolút elérési útja
 * @param string|null $type A vágás típusa: 'product', 'a4_landscape' vagy null (csak tömörítés)
 */
function optimizeImageWithTinify($filePath, $type = null) {
    if (!file_exists($filePath)) return false;

    // API kulcs kinyerése
    $tinifyKey = getenv('TINIFY_API_KEY') ?: ($_ENV['TINIFY_API_KEY'] ?? null);
    
    // Az autoloader útvonala az image_optimizer.php-hoz képest (actions -> fel -> core -> vendor)
    $autoloadPath = dirname(__DIR__) . '/core/vendor/autoload.php';

    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }

    // Ha az autoloader valamiért nem vinné át a Tinify-t, kényszerítsük a fő fájlt
    $manualPath = dirname(__DIR__) . '/core/vendor/tinify/tinify/lib/Tinify.php';
    if (!class_exists('\Tinify\Tinify') && file_exists($manualPath)) {
        require_once $manualPath;
    }

    // Csak akkor futunk le, ha van kulcs ÉS az osztály is betöltődött
    if ($tinifyKey && class_exists('\Tinify\Tinify')) {
        try {
            \Tinify\setKey($tinifyKey);
            
            // Forrásfájl megnyitása
            $source = \Tinify\fromFile($filePath);
            $result = $source;

            // Átméretezési logika
            if ($type === 'product') {
                // Klasszikus 4:3 arány
                $result = $source->resize([
                    "method" => "cover",
                    "width" => 800,
                    "height" => 600
                ]);
            } elseif ($type === 'a4_landscape') {
                // A4 arány fektetve
                $result = $source->resize([
                    "method" => "cover",
                    "width" => 1190,
                    "height" => 842
                ]);
            }

            // Az optimalizált kép mentése (felülírja az eredetit)
            $result->toFile($filePath);
            return true;

        } catch (\Exception $e) {
            // Hibát naplózzuk, de nem állítjuk meg az oldalt
            error_log("Tinify hiba az éles szerveren: " . $e->getMessage());
            return true; 
        }
    }
    
    // Ha nincs Tinify, az eredeti kép marad meg
    return true;
}