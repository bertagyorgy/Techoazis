<?php
// 1. Pufferelés indítása (megelőzi a véletlen whitespace-eket)
ob_start();

// 2. Konfiguráció és adatbázis betöltése
// Feltételezve, hogy ez a fájl az /app/ mappában van, a config pedig a /core/-ban:
require_once __DIR__ . '/../core/config.php'; 
// A config.php-ban valószínűleg már benne van a require_once ROOT_PATH . '/app/db.php';
// Ha nincs, akkor itt is megteheted:
require_once ROOT_PATH . '/app/db.php';

// Most már beállíthatjuk a fejlécet
header('Content-Type: application/json; charset=utf-8');

$response = [];

try {
    // Ellenőrizzük, hogy a db.php-ból megjött-e a $conn objektum
    if (!isset($conn)) {
        throw new Exception("Adatbázis kapcsolat nem jött létre.");
    }

    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

    // 3. Lekérdezés
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM comments WHERE post_id = ?");
    
    if (!$stmt) {
        throw new Exception("SQL Hiba: " . $conn->error);
    }

    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $response['success'] = true;
    $response['count'] = isset($result['count']) ? intval($result['count']) : 0;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['count'] = 0; 
}

// 4. Puffer ürítése (hogy csak a JSON menjen ki)
ob_clean();
echo json_encode($response);
exit();