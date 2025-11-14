<?php
session_start();
include './app/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ./views/login.php");
    exit();
}

$username = $_SESSION['username'];

// Lekérjük a felhasználó adatait
$stmt = $conn->prepare("SELECT user_id, username, profile_image FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$action = $_GET['action'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    if (!empty($user['profile_image']) && $user['profile_image'] !== './images/anonymous.png') {
        if (file_exists($user['profile_image'])) unlink($user['profile_image']);
        $default_image = './images/anonymous.png';
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
        $stmt->bind_param("si", $default_image, $user['user_id']);
        $stmt->execute();
        $stmt->close();
        $message = "Profilkép visszaállítva alapértelmezettre.";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    /* ---------------------- FELHASZNÁLÓNÉV MÓDOSÍTÁS ---------------------- */
    if ($action === 'username') {
        $new_username = trim($_POST['new_username']);
        if (!empty($new_username)) {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_username, $user['user_id']);
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $message = "Felhasználónév sikeresen módosítva!";
            } else {
                $message = "Hiba történt a módosítás során.";
            }
            $stmt->close();
        } else {
            $message = "A felhasználónév nem lehet üres.";
        }
    }

    /* ---------------------- PROFILKÉP MÓDOSÍTÁS ---------------------- */
    if ($action === 'image' && isset($_FILES['profile_image'])) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        $upload_dir = "./uploads/profile_images/";

        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (!in_array($file['type'], $allowed_types)) {
                $message = "Csak JPG, PNG vagy WEBP formátum engedélyezett.";
            } elseif ($file['size'] > $max_size) {
                $message = "A fájl mérete nem haladhatja meg az 5 MB-ot.";
            } else {
                // Ha van régi profilkép, és az nem az anonymous, töröljük
                if (!empty($user['profile_image']) && $user['profile_image'] !== './images/anonymous.png') {
                    $old_path = $user['profile_image'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }

                // Új fájlnév generálása
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_name = "profile_" . $user['user_id'] . "." . $ext;
                $target = $upload_dir . $new_name;

                // Kép méretezése
                list($width, $height) = getimagesize($file['tmp_name']);
                $max_dim = 300;
                $ratio = min($max_dim / $width, $max_dim / $height);
                $new_w = (int)($width * $ratio);
                $new_h = (int)($height * $ratio);

                switch ($file['type']) {
                    case 'image/jpeg': $src = imagecreatefromjpeg($file['tmp_name']); break;
                    case 'image/png':  $src = imagecreatefrompng($file['tmp_name']); break;
                    case 'image/webp': $src = imagecreatefromwebp($file['tmp_name']); break;
                    default: $src = null;
                }

                if ($src) {
                    $dst = imagecreatetruecolor($new_w, $new_h);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $width, $height);

                    // Mentés a fájltípusnak megfelelően
                    if ($file['type'] === 'image/jpeg') imagejpeg($dst, $target, 90);
                    if ($file['type'] === 'image/png') imagepng($dst, $target);
                    if ($file['type'] === 'image/webp') imagewebp($dst, $target, 90);

                    imagedestroy($src);
                    imagedestroy($dst);

                    // Elérési út mentése az adatbázisba
                    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $target, $user['user_id']);
                    $stmt->execute();
                    $stmt->close();

                    $message = "Profilkép sikeresen frissítve!";
                } else {
                    $message = "Érvénytelen képformátum.";
                }
            }
        } else {
            $message = "Hiba történt a fájl feltöltése közben.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil módosítása</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="./static/index.css">
    <script src="./static/index.js" defer></script>
</head>
<body>

<?php include './views/navbar.php'; ?>

<section class="profile-edit-section">
    <div class="profile-edit-container">
        <h2>Profil módosítása</h2>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($action === 'username'): ?>
            <form method="POST">
                <label>Új felhasználónév:</label>
                <input type="text" name="new_username" value="<?= htmlspecialchars($user['username']) ?>" required>
                <button type="submit">Mentés</button>
            </form>

        <?php elseif ($action === 'image'): ?>
            <form method="POST" enctype="multipart/form-data">
                <label>Új profilkép (max. 5MB):</label>
                <input type="file" name="profile_image" accept="image/*" required>
                <button type="submit">Feltöltés</button>
            </form>

            <?php if (!empty($user['profile_image']) && $user['profile_image'] !== './images/anonymous.png'): ?>
                <form method="POST">
                    <input type="hidden" name="delete_image" value="1">
                    <button type="submit" class="delete-btn">Profilkép törlése</button>
                </form>
            <?php endif; ?>

        <?php else: ?>
            <p>Érvénytelen kérés.</p>
        <?php endif; ?>

        <button onclick="window.location.href='profile.php'">Vissza a profilhoz</button>
    </div>
</section>

</body>
</html>
