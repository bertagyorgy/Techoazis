<?php
// profile_edit_logic.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Config betöltése
require_once __DIR__ . '/../core/config.php';

// 2. Adatbázis és segédfüggvények betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/app/helpers.php';

// --- TINIFY ÉS KÖRNYEZETI VÁLTOZÓK BEÁLLÍTÁSA ---
require_once ROOT_PATH . '/core/envreader.php';
loadEnv();
// Az image_optimizer.php behívása a központi helyről
require_once ROOT_PATH . '/actions/image_optimizer.php';

// 3. Biztonsági ellenőrzés
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$username = $_SESSION['username'];
$current_user_id = $_SESSION['user_id'];

// Felhasználó adatainak lekérdezése
$stmt = $conn->prepare("SELECT user_id, username, email, profile_image FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$action = $_GET['action'] ?? 'general';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Profilkép törlése gomb
    if (isset($_POST['delete_image'])) {
        if (!empty($user['profile_image']) && strpos($user['profile_image'], 'anonymous.png') === false) {
            $local_path = ROOT_PATH . '/' . $user['profile_image'];
            if (file_exists($local_path)) {
                unlink($local_path);
            }
        }

        $default_image = 'uploads/profile_images/anonymous.png';
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
        $stmt->bind_param("si", $default_image, $user['user_id']);
        if ($stmt->execute()) {
            $message = "Profilkép visszaállítva alapértelmezettre.";
            $message_type = 'success';
            $user['profile_image'] = $default_image;
        }
        $stmt->close();
    }
    
    // 2. Felhasználónév módosítás
    if (isset($_POST['update_username'])) {
        $new_username = trim($_POST['new_username'] ?? '');
        if (!empty($new_username) && mb_strlen($new_username) >= 3) {
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $check_stmt->bind_param("si", $new_username, $current_user_id);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows === 0) {
                $base_slug = make_slug($new_username);
                if ($base_slug === '') $base_slug = 'user_' . (int)$current_user_id;
                $new_slug = unique_slug($conn, $base_slug, (int)$current_user_id);

                $stmt = $conn->prepare("UPDATE users SET username = ?, username_slug = ? WHERE user_id = ?");
                $stmt->bind_param("ssi", $new_username, $new_slug, $current_user_id);

                if ($stmt->execute()) {
                    $_SESSION['username'] = $new_username;
                    $user['username'] = $new_username;
                    $message = "Felhasználónév sikeresen módosítva!";
                    $message_type = 'success';
                } else {
                    $message = "Hiba történt a módosítás során.";
                    $message_type = 'error';
                }
                $stmt->close();
            } else {
                $message = "Ez a felhasználónév már foglalt.";
                $message_type = 'error';
            }
            $check_stmt->close();
        }
    }

    // 3. Email cím módosítás
    if (isset($_POST['update_email'])) {
        $new_email = trim($_POST['new_email']);
        if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check_stmt->bind_param("si", $new_email, $current_user_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_email, $current_user_id);
                if ($stmt->execute()) {
                    $user['email'] = $new_email;
                    $message = "Email cím sikeresen módosítva!";
                    $message_type = 'success';
                }
                $stmt->close();
            } else {
                $message = "Ez az email cím már foglalt.";
                $message_type = 'error';
            }
            $check_stmt->close();
        }
    }
    
    // 4. Jelszó módosítás (Eredeti logika megtartva)
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($new_password) >= 6 && $new_password === $confirm_password) {
            $stmt = $conn->prepare("SELECT user_password FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $current_user_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if (password_verify($current_password, $res['user_password'])) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $u_stmt = $conn->prepare("UPDATE users SET user_password = ? WHERE user_id = ?");
                $u_stmt->bind_param("si", $hashed, $current_user_id);
                if ($u_stmt->execute()) {
                    $message = "Jelszó sikeresen megváltoztatva!";
                    $message_type = 'success';
                }
            } else {
                $message = "Hibás jelenlegi jelszó.";
                $message_type = 'error';
            }
        }
    }
    
    // 5. Profilkép feltöltés - TINIFY INTEGRÁCIÓVAL
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $upload_dir_path = ROOT_PATH . "/uploads/profile_images/";
            if (!file_exists($upload_dir_path)) mkdir($upload_dir_path, 0777, true);

            // Régi kép törlése
            if (!empty($user['profile_image']) && strpos($user['profile_image'], 'anonymous.png') === false) {
                $old_path = ROOT_PATH . '/' . $user['profile_image'];
                if (file_exists($old_path)) unlink($old_path);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = "profile_" . $current_user_id . "_" . time() . "." . $ext;
            $target_save_path = $upload_dir_path . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $target_save_path)) {
                
                // --- KÉP OPTIMALIZÁLÁSA (Tinify) ---
                optimizeImageWithTinify($target_save_path);
                
                $db_url_path = "uploads/profile_images/" . $new_filename;
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
                $stmt->bind_param("si", $db_url_path, $current_user_id);
                
                if ($stmt->execute()) {
                    $message = "Profilkép sikeresen frissítve!";
                    $message_type = 'success';
                    $user['profile_image'] = $db_url_path;
                }
                $stmt->close();
            }
        } else {
            $message = "Érvénytelen fájlformátum vagy túl nagy méret.";
            $message_type = 'error';
        }
    }
}

$profile_image = !empty($user['profile_image']) ? BASE_URL . '/' . $user['profile_image'] : BASE_URL . '/uploads/profile_images/anonymous.png';