<?php
// 1. Config behívása a ROOT_PATH és konstansok miatt
// Mivel ez az app mappában van, egy szintet fel kell lépni (..)
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Adatbázis behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/db.php';

// A kimenet típusa mindig JSON
header('Content-Type: application/json');

$user_id = $_GET['user_id'] ?? $_SESSION['user_id'] ?? 0;
// Biztonsági ellenőrzés: ha nincs user_id, ne is fusson tovább
if ($user_id == 0) {
    echo json_encode(['hasNewMessages' => false, 'count' => 0]);
    exit;
}

$last_check = $_GET['last_check'] ?? time();

$stmt = $conn->prepare("
    SELECT COUNT(*) as new_messages 
    FROM messages m
    JOIN conversations c ON m.conversation_id = c.conversation_id
    WHERE (c.seller_user_id = ? OR c.buyer_user_id = ?)
    AND m.sent_at > FROM_UNIXTIME(?)
    AND m.sender_user_id != ?
    AND m.is_read = 0
");

$stmt->bind_param('iiii', $user_id, $user_id, $last_check, $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'hasNewMessages' => (int)$result['new_messages'] > 0,
    'count' => (int)$result['new_messages']
]);

$stmt->close();
$conn->close();
?>