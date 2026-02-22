<?php
// actions/image_optimizer.php

/**
 * Kép optimalizálása Tinify API-val
 * @param string $filePath A kép abszolút elérési útja
 * @param string|null $type A vágás típusa: 'product', 'a4_landscape' vagy null (csak tömörítés)
 */
function optimizeImageWithTinify($filePath, $type = null) {
    if (!file_exists($filePath)) return false;

    $tinifyKey = getenv('TINIFY_API_KEY') ?: ($_ENV['TINIFY_API_KEY'] ?? null);
    $autoloadPath = ROOT_PATH . '/core/vendor/autoload.php';

    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }

    if ($tinifyKey && class_exists('\Tinify\Tinify')) {
        try {
            \Tinify\setKey($tinifyKey);
            $source = \Tinify\fromFile($filePath);

            // Alapértelmezés: Csak a forrás (nincs átméretezés)
            $result = $source;

            // Ha van megadva típus, akkor vágunk is
            if ($type === 'product') {
                // Klasszikus 4:3 fekvő arány
                $result = $source->resize([
                    "method" => "cover",
                    "width" => 800,
                    "height" => 600
                ]);
            } elseif ($type === 'a4_landscape') {
                // A4-es papír arány fektetve (kb. 1.41 : 1)
                $result = $source->resize([
                    "method" => "cover",
                    "width" => 1190,
                    "height" => 842
                ]);
            }

            // Mentés (vagy az eredeti méretben, vagy a vágottban)
            $result->toFile($filePath);
            return true;
        } catch (\Exception $e) {
            error_log("Tinify hiba: " . $e->getMessage());
            return true; 
        }
    }
    return true;
}