<?php
// 1. Config behívása a konstansok (ROOT_PATH, BASE_URL) miatt
// Ha az index.php routeren keresztül jössz, ez már be van töltve, 
// de a biztonság kedvéért (ha közvetlen linkről jönnek) érdemes így hagyni:
require_once __DIR__ . '/../core/config.php';

// 2. Adatbázis behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/db.php';

$email = $_GET['email'] ?? '';
$code = $_GET['code'] ?? '';
$message = "";

if (empty($email) || empty($code)) {
    $message = "Hiba: Hiányzó aktiválási adatok.";
} else {
    // 1. Keresd meg a felhasználót az email és a kód alapján
    $sql = "SELECT user_id, is_active FROM users WHERE email = ? AND activation_code = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            
            // 2. Ellenőrizd, hogy nincs-e már aktív
            if ($row['is_active'] === 'A') {
                $message = "A fiókod már aktív. Bejelentkezhetsz.";
            } else {
                // 3. Aktiváld a fiókot (is_active -> 'A') és töröld az aktiváló kódot
                $update_sql = "UPDATE users SET is_active = 'A', activation_code = NULL WHERE user_id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("i", $row['user_id']);
                    if ($update_stmt->execute()) {
                        $message = "Gratulálunk! A fiókod sikeresen aktiválva lett. Most már bejelentkezhetsz.";
                    } else {
                        $message = "Hiba történt a fiók aktiválása során.";
                    }
                    $update_stmt->close();
                }
            }
        } else {
            $message = "Érvénytelen aktiválási link.";
        }
        $stmt->close();
    } else {
        $message = "Adatbázis hiba.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Fiók Aktiválása | Techoázis</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <style>
        .activation-container { text-align: center; padding: 50px; font-family: 'Inter', sans-serif; }
        .btn-login { background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="activation-container">
        <h1>Fiók Aktiválása</h1>
        <p><?php echo $message; ?></p>
        
        <p><a class="btn-login" href="<?= BASE_URL ?>/login">Tovább a bejelentkezéshez</a></p>
    </div>
</body>
</html>