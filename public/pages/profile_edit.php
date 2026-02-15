<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// 1. Config betöltése (ez hozza létre a ROOT_PATH-ot és a BASE_URL-t)
require_once __DIR__ . '/../core/config.php';

// 2. Adatbázis betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/app/helpers.php';

// 3. Biztonsági ellenőrzés javítása BASE_URL-lel
if (!isset($_SESSION['username'])) {
    // PHP alapú átirányítás biztonságosabb és tisztább
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
        // Csak akkor töröljünk, ha van kép és nem az alapértelmezett
        if (!empty($user['profile_image']) && strpos($user['profile_image'], 'anonymous.png') === false) {
            
            // FONTOS: Az URL-t át kell alakítani helyi elérési úttá a törléshez!
            $local_path = str_replace(BASE_URL, ROOT_PATH, $user['profile_image']);
            
            if (file_exists($local_path)) {
                unlink($local_path);
            }
        }

        // Adatbázis visszaállítása
        $default_image = BASE_URL . '/uploads/profile_images/anonymous.png';
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
        $stmt->bind_param("si", $default_image, $user['user_id']);
        if ($stmt->execute()) {
            $message = "Profilkép visszaállítva alapértelmezettre.";
            $message_type = 'success';
            $user['profile_image'] = $default_image; // Session frissítése azonnal
        }
        $stmt->close();
    }
    
    // 2. Felhasználónév módosítás ( változatlan )
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
                        $user['username'] = $new_username; // Frissítjük a helyi változót is
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

    // 3. Email cím módosítás ( változatlan )
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
                    $user['email'] = $new_email; // Frissítés
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
    
    // 4. Jelszó módosítás ( változatlan )
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
    
    // 5. Profilkép feltöltés (JAVÍTOTT RÉSZ)
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        // JAVÍTÁS: Itt ROOT_PATH-ot használunk a fájlrendszer eléréshez
        $upload_dir_path = ROOT_PATH . "/uploads/profile_images/";
        // Ez pedig az URL lesz az adatbázisba
        $upload_dir_url = "uploads/profile_images/";
        
        if (!file_exists($upload_dir_path)) {
            mkdir($upload_dir_path, 0777, true);
        }
        
        if (in_array($file['type'], $allowed_types)) {
            if ($file['size'] <= $max_size) {
                
                // Régi kép törlése (URL -> Fájl útvonal konverzióval)
                if (!empty($user['profile_image']) && strpos($user['profile_image'], 'anonymous.png') === false) {
                    $old_file_path = str_replace(BASE_URL, ROOT_PATH, $user['profile_image']);
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
                
                // Új fájlnév
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = "profile_" . $current_user_id . "_" . time() . "." . $ext;
                
                // JAVÍTÁS: A mentési célútvonal a fizikai elérési út legyen!
                $target_save_path = $upload_dir_path . $new_filename;
                
                // Kép feldolgozás
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
                    
                    // Mentés (most már a fizikai útvonalra írunk)
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
                        // Adatbázis frissítése (itt marad az URL!)
                        $db_url_path = $upload_dir_url . $new_filename;
                        
                        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
                        $stmt->bind_param("si", $db_url_path, $current_user_id);
                        
                        if ($stmt->execute()) {
                            $message = "Profilkép sikeresen frissítve!";
                            $message_type = 'success';
                            $user['profile_image'] = $db_url_path; // View frissítése
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
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil szerkesztése</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile_edit_style.css">


    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>


</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

<div class="profile-edit-container">
    <div class="profile-edit-card">
    <div class="profile-edit-header">
        <h1>Profil szerkesztése</h1>
        <a href="<?= BASE_URL ?>/pages/profile.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Vissza a profilhoz
        </a>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="profile-edit-nav">
        <button class="nav-tab <?php echo $action === 'general' ? 'active' : ''; ?>" data-section="general">
            <i class="fas fa-user"></i> Alapadatok
        </button>
        <button class="nav-tab <?php echo $action === 'image' ? 'active' : ''; ?>" data-section="image">
            <i class="fas fa-image"></i> Profilkép
        </button>
        <button class="nav-tab <?php echo $action === 'password' ? 'active' : ''; ?>" data-section="password">
            <i class="fas fa-lock"></i> Jelszó
        </button>
        <button class="nav-tab <?php echo $action === 'security' ? 'active' : ''; ?>" data-section="security">
            <i class="fas fa-shield-alt"></i> Biztonság
        </button>
    </div>

    <!-- Alapadatok szerkesztése -->
    <section id="general-section" class="edit-section <?php echo $action === 'general' ? 'active' : ''; ?>">
        
        <!-- Felhasználónév módosítása -->
        <form method="POST" class="edit-form">
            <input type="hidden" name="update_username" value="1">
            
            <div class="form-group">
                <label for="current_username">Jelenlegi felhasználónév:</label>
                <input type="text" id="current_username" class="form-control" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="new_username">Új felhasználónév:</label>
                <input type="text" id="new_username" name="new_username" class="form-control" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3" maxlength="100">
                <div class="form-hint">Legalább 3 karakter</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Felhasználónév mentése
                </button>
            </div>
        </form>
        
        <hr style="margin: 2rem 0; border-color: var(--border-color);">
        
        <!-- Email cím módosítása -->
        <form method="POST" class="edit-form">
            <input type="hidden" name="update_email" value="1">
            
            <div class="form-group">
                <label for="current_email">Jelenlegi email cím:</label>
                <input type="email" id="current_email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="new_email">Új email cím:</label>
                <input type="email" id="new_email" name="new_email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <div class="form-hint">Érvényes email címet adj meg</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Email cím mentése
                </button>
            </div>
        </form>
    </section>

    <!-- Profilkép módosítása -->
    <section id="image-section" class="edit-section <?php echo $action === 'image' ? 'active' : ''; ?>">
        
        <div class="edit-form" style="text-align: center;">
            <img src="<?php echo $profile_image; ?>" alt="Profilkép előnézet" class="image-preview" 
                 onerror="this.src='<?= BASE_URL ?>/uploads/profile_images/anonymous.png'">
            
            <form method="POST" enctype="multipart/form-data">
                <div class="file-upload">
                    <label class="file-upload-label">
                        <i class="fas fa-upload"></i> Kép kiválasztása
                        <input type="file" name="profile_image" accept="image/*" required onchange="previewImage(this)">
                    </label>
                </div>
                
                <div class="form-hint">Max. 5MB, JPG, PNG, WEBP vagy GIF formátum</div>
                
                <div class="form-actions" style="justify-content: center;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Feltöltés
                    </button>
                </div>
            </form>
            
        </div>
    </section>

    <!-- Jelszó módosítása -->
    <section id="password-section" class="edit-section <?php echo $action === 'password' ? 'active' : ''; ?>">
        
        <form method="POST" class="edit-form">
            <input type="hidden" name="update_password" value="1">
            
            <div class="form-group">
                <label for="current_password">Jelenlegi jelszó:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password">Új jelszó:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-hint">Legalább 6 karakter</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Új jelszó megerősítése:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-key"></i> Jelszó megváltoztatása
                </button>
            </div>
        </form>
    </section>

    <!-- Biztonsági beállítások -->
    <section id="security-section" class="edit-section <?php echo $action === 'security' ? 'active' : ''; ?>">
        
        <div class="edit-form">
            <div class="form-group">
                <h3 style="color: var(--danger); margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-triangle"></i> Veszélyes műveletek
                </h3>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4>Funkciók:</h4>
                    <p style="color: var(--text-light); margin-bottom: 1rem;">
                        Ezen funkciók végrehajtása előtt gondosan gondold át döntésedet!
                    </p>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <button type="button" class="delete-image-btn" onclick="confirmDeleteAccount()">
                        <i class="fas fa-user-slash"></i> Fiók végleges törlése
                    </button>
                </div>
            </div>
        </div>
    </section>
    </div>
</div>

<script>
// Tab váltás
document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const sectionId = this.dataset.section + '-section';
        
        // Tabok aktív állapotának frissítése
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // Section-ök megjelenítése/elrejtése
        document.querySelectorAll('.edit-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(sectionId).classList.add('active');
        
        // URL frissítése
        history.pushState(null, null, `?action=${this.dataset.section}`);
    });
});

// Profilkép előnézet
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.image-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Jelszó mutatása/elrejtése
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        toggle.className = 'fas fa-eye';
    }
}

// Fiók törlés megerősítése
function confirmDeleteAccount() {
    if (confirm('⚠️ VIGYÁZAT!\n\nA fiók törlésével:\n• Minden adatod véglegesen törlődik\n• Termékeid eltűnnek\n• Beszélgetéseid törlődnek\n• Nem vonható vissza!\n\nBiztos, hogy folytatod?')) {
        // Itt lehetne AJAX hívás vagy form beküldés
        window.location.href = '<?= BASE_URL ?>/app/delete_account.php';
    }
}
</script>
</body>
</html>
<?php $conn->close(); ?>