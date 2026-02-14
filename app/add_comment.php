<?php
// 1. Kimenet pufferelés indítása (megelőzi a véletlen szóközök okozta JSON hibát)
ob_start();

// 2. Hibakezelés és útvonalak beállítása
$response = [];

try {
    // Meghatározzuk a config.php elérési útját az aktuális mappához képest
    // app/add_comment.php -> fel egyet -> core/config.php
    $configPath = dirname(__DIR__) . '/core/config.php';

    if (!file_exists($configPath)) {
        throw new Exception("Rendszerhiba: A konfigurációs fájl nem található.");
    }

    // Config betöltése
    require_once $configPath;

    // Ha a config.php-ban nincs session_start(), elindítjuk itt
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // JSON fejléc (Csak a require után, hogy ne legyen gond, ha a configban van hiba)
    header("Content-Type: application/json; charset=utf-8");

    // Szükséges további fájlok betöltése (a config.php-ban definiált ROOT_PATH használatával)
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(__DIR__)); // Biztonsági mentés, ha a configból hiányozna
    }

    require_once ROOT_PATH . '/app/db.php';
    require_once ROOT_PATH . '/app/profile_stats.php';

    // 3. Bejelentkezés ellenőrzése
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Be kell jelentkezned a kommenteléshez!");
    }

    // 4. Bemenő adatok feldolgozása
    $user_id = $_SESSION['user_id'];
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : "";

    // Validálás
    if ($post_id <= 0 || empty($content)) {
        throw new Exception("Hiányzó adatok vagy üres hozzászólás!");
    }

    // 5. Adatbázis művelet (INSERT)
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    
    if (!$stmt) {
        throw new Exception("SQL előkészítési hiba: " . $conn->error);
    }

    $stmt->bind_param("iis", $post_id, $user_id, $content);

    if ($stmt->execute()) {
        // Statisztika frissítése (ha van ilyen funkció a profile_stats.php-ban)
        if (function_exists('refreshUserStats')) {
            refreshUserStats($conn, $user_id);
        }

        $response['success'] = true;
        $response['message'] = "Komment sikeresen hozzáadva.";
    } else {
        throw new Exception("Hiba a mentés során: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    // Bármilyen hiba esetén JSON formátumban válaszolunk
    header("Content-Type: application/json; charset=utf-8");
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// 6. Kimenet tisztítása és küldése
// Csak a tiszta JSON kód kerülhet a kimenetre!
ob_clean();
echo json_encode($response);
exit();
?>