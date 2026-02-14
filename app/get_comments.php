<?php
// 1. Config behívása a konstansok (BASE_URL, ROOT_PATH) miatt
require_once __DIR__ . '/../core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Adatbázis behívása ROOT_PATH-al
require_once ROOT_PATH . '/app/db.php';

header("Content-Type: application/json");

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
    // 3. JAVÍTÁS: Kép útvonalak abszolútítása BASE_URL-el
    if (!$row['profile_image'] || empty($row['profile_image'])) {
        // Alapértelmezett kép fix útvonala
        $row['profile_image'] = BASE_URL . "/images/anonymous.png";
    } else {
        // Ha van saját kép, az elé is tesszük a BASE_URL-t
        // (Feltéve, hogy az adatbázisban pl. 'images/profiles/user1.jpg' van)
        $row['profile_image'] = BASE_URL . "/" . $row['profile_image'];
    }
    $comments[] = $row;
}

echo json_encode(["success" => true, "comments" => $comments]);