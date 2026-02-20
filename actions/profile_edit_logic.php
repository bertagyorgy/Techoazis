<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Config betöltése
require_once __DIR__ . '/../core/config.php';

// 2. Adatbázis és segédfüggvények betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/app/helpers.php';

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
            $local_path = str_replace(BASE_URL, ROOT_PATH, $user['profile_image']);
            if (file_exists($local_path)) {
                unlink($local_path);
            }
        }

        $default_image = BASE_URL . '/uploads/profile_images/anonymous.png';
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

                $slug_stmt = $conn->prepare("SELECT user_id FROM users WHERE username_slug = ? AND user_id != ?");
                $slug_stmt->bind_param("si", $new_slug, $current_user_id);
                $slug_stmt->execute();

                if ($slug_stmt->get_result()->num_rows === 0) {
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
                    $message = "Ez a felhasználónév (slug) már foglalt.";
                    $message_type = 'error';
                }
                $slug_stmt->close();
            } else {
                $message = "Ez a felhasználónév már foglalt.";
                $message_type = 'error';
            }
            $check_stmt->close();
        } else {
            $message = "A felhasználónév legalább 3 karakter hosszú kell legyen.";
            $message_type = 'error';
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
                } else {
                    $message = "Hiba történt a módosítás során.";
                    $message_type = 'error';
                }
                $stmt->close();
            } else {
                $message = "Ez az email cím már regisztrálva van.";
                $message_type = 'error';
            }
            $check_stmt->close();
        } else {
            $message = "Érvénytelen email cím.";
            $message_type = 'error';
        }
    }
    
    // 4. Jelszó módosítás
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($new_password) >= 6) {
            if ($new_password === $confirm_password) {
                $stmt = $conn->prepare("SELECT user_password FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $current_user_id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if (password_verify($current_password, $result['user_password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET user_password = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $hashed_password, $current_user_id);
                    if ($stmt->execute()) {
                        $message = "Jelszó sikeresen megváltoztatva!";
                        $message_type = 'success';
                    } else {
                        $message = "Hiba történt a jelszó módosítása során.";
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = "Hibás jelenlegi jelszó.";
                    $message_type = 'error';
                }
            } else {
                $message = "Az új jelszavak nem egyeznek.";
                $message_type = 'error';
            }
        } else {
            $message = "Az új jelszó legalább 6 karakter hosszú kell legyen.";
            $message_type = 'error';
        }
    }
    
    // 5. Profilkép feltöltés
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        $upload_dir_path = ROOT_PATH . "/uploads/profile_images/";
        $upload_dir_url = "uploads/profile_images/";
        
        if (!file_exists($upload_dir_path)) {
            mkdir($upload_dir_path, 0777, true);
        }
        
        if (in_array($file['type'], $allowed_types)) {
            if ($file['size'] <= $max_size) {
                if (!empty($user['profile_image']) && strpos($user['profile_image'], 'anonymous.png') === false) {
                    $old_file_path = str_replace(BASE_URL, ROOT_PATH, $user['profile_image']);
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
                
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = "profile_" . $current_user_id . "_" . time() . "." . $ext;
                $target_save_path = $upload_dir_path . $new_filename;
                
                list($width, $height) = getimagesize($file['tmp_name']);
                $new_width = 300;
                $new_height = 300;
                
                $image = null;
                switch ($file['type']) {
                    case 'image/jpeg': $image = imagecreatefromjpeg($file['tmp_name']); break;
                    case 'image/png': $image = imagecreatefrompng($file['tmp_name']); break;
                    case 'image/webp': $image = imagecreatefromwebp($file['tmp_name']); break;
                    case 'image/gif': $image = imagecreatefromgif($file['tmp_name']); break;
                }
                
                if ($image) {
                    $resized = imagecreatetruecolor($new_width, $new_height);
                    
                    if ($file['type'] === 'image/png' || $file['type'] === 'image/gif') {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                        imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
                    }
                    
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    
                    $save_success = false;
                    switch ($file['type']) {
                        case 'image/jpeg': $save_success = imagejpeg($resized, $target_save_path, 90); break;
                        case 'image/png': $save_success = imagepng($resized, $target_save_path, 9); break;
                        case 'image/webp': $save_success = imagewebp($resized, $target_save_path, 90); break;
                        case 'image/gif': $save_success = imagegif($resized, $target_save_path); break;
                    }
                    
                    imagedestroy($image);
                    imagedestroy($resized);
                    
                    if ($save_success) {
                        $db_url_path = $upload_dir_url . $new_filename;
                        
                        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
                        $stmt->bind_param("si", $db_url_path, $current_user_id);
                        
                        if ($stmt->execute()) {
                            $message = "Profilkép sikeresen frissítve!";
                            $message_type = 'success';
                            $user['profile_image'] = $db_url_path;
                        } else {
                            $message = "Hiba az adatbázis frissítése során.";
                            $message_type = 'error';
                        }
                        $stmt->close();
                    } else {
                        $message = "Nem sikerült a képet elmenteni a szerverre.";
                        $message_type = 'error';
                    }
                } else {
                    $message = "Hiba a kép feldolgozása során.";
                    $message_type = 'error';
                }
            } else {
                $message = "A fájl mérete túl nagy (max. 5MB).";
                $message_type = 'error';
            }
        } else {
            $message = "Csak JPG, PNG, WEBP vagy GIF formátumok engedélyezettek.";
            $message_type = 'error';
        }
    }
}

$profile_image = !empty($user['profile_image']) ? htmlspecialchars(BASE_URL . '/' . $user['profile_image']) : BASE_URL . '/uploads/profile_images/anonymous.png';