<?php
// 1. JSON fejléc és pufferelés
ob_start();
header('Content-Type: application/json; charset=utf-8');

$response = [];

try {
    // 2. Adatbázis biztonságos betöltése
    if (!file_exists(__DIR__ . '/db.php')) {
        throw new Exception("Adatbázis fájl nem található.");
    }
    require_once __DIR__ . '/db.php';

    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

    // 3. Lekérdezés
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM comments WHERE post_id = ?");
    
    if (!$stmt) {
        throw new Exception("SQL Hiba: " . $conn->error);
    }

    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // 4. Eredmény beállítása (biztosítjuk, hogy integer legyen)
    $response['success'] = true;
    $response['count'] = isset($result['count']) ? intval($result['count']) : 0;

} catch (Exception $e) {
    // Hiba esetén is valid JSON-t küldünk, 0 darab kommenttel
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['count'] = 0; 
}

// 5. Válasz küldése
ob_clean();
echo json_encode($response);
exit();
?>