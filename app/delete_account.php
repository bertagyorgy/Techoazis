<?php
session_start();
include './db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Transaction kezdése
$conn->begin_transaction();

try {
    // 1. Képek törlése
    $stmt = $conn->prepare("SELECT image_path FROM images WHERE product_id IN (SELECT product_id FROM products WHERE seller_user_id = ?)");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $images = $stmt->get_result();
    while ($image = $images->fetch_assoc()) {
        if (file_exists($image['image_path'])) {
            unlink($image['image_path']);
        }
    }
    $stmt->close();
    
    // 2. Profilkép törlése
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $profile_image = $stmt->get_result()->fetch_assoc()['profile_image'];
    $stmt->close();
    
    if ($profile_image && $profile_image !== './images/anonymous.png') {
        if (file_exists($profile_image)) {
            unlink($profile_image);
        }
    }
    
    // 3. Táblákból való törlés (fontossági sorrendben)
    $tables = [
        'messages' => 'conversation_id IN (SELECT conversation_id FROM conversations WHERE seller_user_id = ? OR buyer_user_id = ?)',
        'deals' => 'seller_user_id = ? OR buyer_user_id = ?',
        'conversations' => 'seller_user_id = ? OR buyer_user_id = ?',
        'reviews' => 'seller_user_id = ? OR buyer_user_id = ?',
        'images' => 'product_id IN (SELECT product_id FROM products WHERE seller_user_id = ?)',
        'products' => 'seller_user_id = ?',
        'comments' => 'user_id = ?',
        'posts' => 'user_id = ?',
        'user_badges' => 'user_id = ?',
        'login' => 'user_id = ?',
        'users' => 'user_id = ?'
    ];
    
    foreach ($tables as $table => $condition) {
        if (strpos($condition, '? ?') !== false) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE $condition");
            $stmt->bind_param('ii', $user_id, $user_id);
        } else {
            $stmt = $conn->prepare("DELETE FROM $table WHERE $condition");
            $stmt->bind_param('i', $user_id);
        }
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->commit();
    
    // Session törlése
    session_destroy();
    
    // Sikeres törlés üzenet
    session_start();
    $_SESSION['message'] = 'Fiókod sikeresen törölve.';
    header('Location: ../index.php');
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Account deletion failed: " . $e->getMessage());
    header('Location: ../profile.php?error=' . urlencode('Hiba történt a fiók törlése során.'));
}
?>