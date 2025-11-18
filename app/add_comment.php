<?php
session_start();
include "../app/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["success" => false, "message" => "Be kell jelentkezned!"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;
$content = trim($_POST['content'] ?? "");

if (!$post_id || $content === "") {
    echo json_encode(["success" => false, "message" => "Hiányzó adat!"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $content);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "DB hiba"]);
}

$stmt->close();
$conn->close();
