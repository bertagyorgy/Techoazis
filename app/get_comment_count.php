<?php
require_once "db.php";

$post_id = intval($_GET['post_id']);

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM comments WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    "success" => true,
    "count" => $result['count']
]);
