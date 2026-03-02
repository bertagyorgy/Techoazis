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

try {
    $conn->begin_transaction();

    $sql = "UPDATE users SET is_active = 'T' WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // EZ HIÁNYZOTT: Véglegesítjük a módosítást
    $conn->commit();

    // Siker után illik kiléptetni a felhasználót
    session_unset();
    session_destroy();

    // Átirányítás a főoldalra vagy egy búcsú oldalra
    header('Location: ' . BASE_URL . '/index?status=deleted');
    exit();

} catch (Exception $e) {
    // Hiba esetén visszavonjuk a megkezdett folyamatot
    $conn->rollback();
    error_log("Account deletion failed: " . $e->getMessage());
    header('Location: ' . BASE_URL . '/pages/profile?error=' . urlencode('Hiba történt a fiók törlése során.'));
    exit();
}
?>