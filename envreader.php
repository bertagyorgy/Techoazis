<?php
function loadEnv(string $path = __DIR__.'/.env'): void {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\" \t\n\r\0\x0B");

        // Mentés $_ENV-be
        $_ENV[$key] = $value;

        // Beállítás környezeti változóként
        putenv("$key=$value");
    }
}
?>
