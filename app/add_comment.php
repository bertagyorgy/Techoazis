<?php
// Kimenet pufferelés indítása (hogy ne kerüljön véletlen szóköz a JSON elé)
ob_start();
session_start();

// JSON fejléc beállítása
header("Content-Type: application/json; charset=utf-8");

$response = [];

try {
    // 1. Bejelentkezés ellenőrzése
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        throw new Exception("Be kell jelentkezned a kommenteléshez!");
    }

    // 2. Adatbázis fájl betöltése (Biztonságos útvonal: __DIR__)
    // Ez azt jelenti: "keresd a db.php-t ugyanabban a mappában, ahol én vagyok"
    if (!file_exists(__DIR__ . '/db.php')) {
        throw new Exception("Rendszerhiba: Az adatbázis fájl nem található.");
    }
    require_once __DIR__ . '/db.php';

    // 3. Bemenő adatok feldolgozása
    // A $_SESSION['user_id']-t feltételezzük, hogy létezik, ha loggedin true
    $user_id = $_SESSION['user_id'] ?? 0;
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $content = trim($_POST['content'] ?? "");

    // Validálás
    if ($post_id <= 0 || $content === "") {
        throw new Exception("Hiányzó adat vagy üres komment!");
    }

    if ($user_id <= 0) {
        throw new Exception("Érvénytelen felhasználói azonosító. Jelentkezz be újra!");
    }

    // 4. Adatbázis művelet
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    
    if (!$stmt) {
        throw new Exception("SQL Hiba: " . $conn->error);
    }

    $stmt->bind_param("iis", $post_id, $user_id, $content);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Komment sikeresen elküldve.";
    } else {
        throw new Exception("Nem sikerült menteni a kommentet: " . $stmt->error);
    }

    $stmt->close();
    // A db kapcsolatot nem feltétlen kell lezárni, a PHP megteszi a script végén, 
    // de ha akarod: $conn->close();

} catch (Exception $e) {
    // Bármi hiba történt, itt elkapjuk
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// 5. Kimenet küldése
// Puffer törlése (hogy csak a tiszta JSON menjen ki)
ob_clean();
echo json_encode($response);
exit();
?>