<?php
// 1. Config behívása a konstansok (BASE_URL, ROOT_PATH) miatt

error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
require_once __DIR__ . '/../core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// 2. Adatbázis behívása ROOT_PATH-al
require_once ROOT_PATH . '/app/db.php';


$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    echo json_encode(["success" => false, "message" => "Nincs post_id"]);
    exit();
}

$query = "
    SELECT c.comment_id, c.content, c.created_at,
           u.username, u.profile_image
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];

while ($row = $result->fetch_assoc()) {
    // 1. Ellenőrizzük, hogy üres-e a profilkép
    if (!$row['profile_image'] || empty($row['profile_image'])) {
        // Alapértelmezett kép, ha nincs semmi megadva
        $row['profile_image'] = BASE_URL . "/images/anonymous.png";
    } else {
        // 2. Megnézzük, hogy külső URL-e (pl. DiceBear)
        // Ha http-vel vagy https-el kezdődik, nem fűzzük hozzá a BASE_URL-t
        if (preg_match('/^https?:\/\//', $row['profile_image'])) {
            // Külső link esetén marad az eredeti (htmlspecialchars-el védve)
            $row['profile_image'] = htmlspecialchars($row['profile_image']);
        } else {
            // 3. Ha helyi fájl, akkor hozzátesszük a BASE_URL-t
            $row['profile_image'] = BASE_URL . "/" . $row['profile_image'];
        }
    }
    
    $comments[] = $row;
}
if (ob_get_length()) ob_clean();
echo json_encode(["success" => true, "comments" => $comments]);
exit();