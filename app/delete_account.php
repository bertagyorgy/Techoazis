<?php
// 1. Config betöltése a konstansok (ROOT_PATH, BASE_URL) miatt
require_once __DIR__ . '/../core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Adatbázis behívása ROOT_PATH-al
require_once ROOT_PATH . '/app/db.php';

if (!isset($_SESSION['user_id'])) {
    // JAVÍTÁS: Szép URL használata az átirányításhoz
    header('Location: ' . BASE_URL . '/login');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn->begin_transaction();

try {
    // 1. Termékképek törlése - ROOT_PATH használata a törléshez!
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id IN (SELECT product_id FROM products WHERE seller_user_id = ?)");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $images = $stmt->get_result();
    while ($image = $images->fetch_assoc()) {
        // JAVÍTÁS: A ROOT_PATH garantálja, hogy a fájlrendszerben jó helyen keresünk
        $full_path = ROOT_PATH . '/' . $image['image_path'];
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
    $stmt->close();
    
    // 2. Profilkép törlése
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $profile_image = $stmt->get_result()->fetch_assoc()['profile_image'];
    $stmt->close();
    
    if ($profile_image && $profile_image !== 'images/anonymous.png') {
        $full_profile_path = ROOT_PATH . '/' . $profile_image;
        if (file_exists($full_profile_path)) {
            unlink($full_profile_path);
        }
    }
    
    // 3. Táblákból való törlés (A logika marad, de az előkészítés biztosabb)
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
        $stmt = $conn->prepare("DELETE FROM $table WHERE $condition");
        if (strpos($condition, 'OR') !== false) {
            $stmt->bind_param('ii', $user_id, $user_id);
        } else {
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
    // JAVÍTÁS: Átirányítás a főoldalra szép URL-el
    header('Location: ' . BASE_URL);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Account deletion failed: " . $e->getMessage());
    // JAVÍTÁS: Átirányítás a profilra hiba esetén szép URL-el
    header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Hiba történt a fiók törlése során.'));
}
?>