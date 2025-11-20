<?php
session_start();
include "../app/db.php";

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
    // alap profilkép fallback
    if (!$row['profile_image'] || $row['profile_image'] === "") {
        $row['profile_image'] = "./images/anonymous.png";
    }
    $comments[] = $row;
}

echo json_encode(["success" => true, "comments" => $comments]);
