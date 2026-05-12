<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conv_id']) && isset($_SESSION['user_id'])) {
    $conv_id = (int)$_POST['conv_id'];
    $user_id = (int)$_SESSION['user_id'];

    // Megnézzük, eladó vagy vevő-e a user az adott chatben
    $stmt = $conn->prepare("UPDATE conversations SET 
        seller_archived = CASE WHEN seller_user_id = ? THEN 1 ELSE seller_archived END,
        buyer_archived = CASE WHEN buyer_user_id = ? THEN 1 ELSE buyer_archived END
        WHERE conversation_id = ? AND (seller_user_id = ? OR buyer_user_id = ?)");
    
    $stmt->bind_param("iiiii", $user_id, $user_id, $conv_id, $user_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}