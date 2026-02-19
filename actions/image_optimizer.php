<?php
// actions/image_optimizer.php

/**
 * Kép optimalizálása Tinify API-val
 * * @param string $filePath A kép abszolút elérési útja a szerveren
 * @return bool Igaz, ha sikerült (vagy ha nem volt API kulcs, de a fájl létezik), hamis hiba esetén
 */
function optimizeImageWithTinify($filePath) {
    // Ha a fájl nem létezik, nincs mit tenni
    if (!file_exists($filePath)) {
        return false;
    }

    // Szükséges függőségek betöltése (config és env már be van töltve a hívó fájlban)
    $tinifyKey = getenv('TINIFY_API_KEY') ?: ($_ENV['TINIFY_API_KEY'] ?? null);
    $autoloadPath = ROOT_PATH . '/core/vendor/autoload.php';

    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }

    // Ha van kulcs és be van töltve a könyvtár, mehet az optimalizálás
    if ($tinifyKey && class_exists('\Tinify\Tinify')) {
        try {
            \Tinify\setKey($tinifyKey);
            $source = \Tinify\fromFile($filePath);
            $source->toFile($filePath);
            return true;
        } catch (\Exception $e) {
            // Hiba esetén (pl. hálózati hiba vagy elfogyott keret) 
            // az eredeti fájl megmarad, így true-val térünk vissza, hogy a folyamat ne álljon le
            error_log("Tinify hiba: " . $e->getMessage());
            return true; 
        }
    }

    // Ha nincs API kulcs, akkor is "sikeres", hiszen a fájl ott van eredetiben
    return true;
}