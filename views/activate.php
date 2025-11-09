<?php
include '../app/db.php';

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
<html>
<head>
    <title>Fiók Aktiválása</title>
</head>
<body>
    <h1>Fiók Aktiválása</h1>
    <p><?php echo $message; ?></p>
    <p><a href="../views/login.php">Tovább a bejelentkezéshez</a></p>
</body>
</html>