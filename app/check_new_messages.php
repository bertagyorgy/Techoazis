<?php
session_start();
include './db.php';

$user_id = $_GET['user_id'] ?? $_SESSION['user_id'] ?? 0;
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
    'hasNewMessages' => $result['new_messages'] > 0,
    'count' => $result['new_messages']
]);
$stmt->close();
$conn->close();
?>