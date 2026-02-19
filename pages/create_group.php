<?php
// create_group.php
ob_start(); // Kimeneti pufferelés indítása a "headers already sent" hibák elkerülésére
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

// Környezeti változók betöltése a Tinify kulcshoz
require_once ROOT_PATH . '/core/envreader.php';
loadEnv();

// --- AZ ÚJ OPTIMALIZÁLÓ MODUL BEHÍVÁSA ---
require_once ROOT_PATH . '/actions/image_optimizer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$error_msg = "";
$uploadDirAbs = ROOT_PATH . '/uploads/groups/';

// Mappa ellenőrzése és létrehozása
if (!is_dir($uploadDirAbs)) {
    @mkdir($uploadDirAbs, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name'] ?? '');
    $group_description = trim($_POST['group_description'] ?? '');
    $group_image = 'default_group.png'; // Alapértelmezett érték

    if (empty($group_name)) {
        $error_msg = "A csoport nevének megadása kötelező!";
    } else {
        // --- Képfeltöltés kezelése ---
        if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === UPLOAD_ERR_OK) {
            
            // --- MÉRET LIMITÁLÁS (5MB) ---
            $maxFileSize = 5 * 1024 * 1024; // 5 Megabájt bájtban számolva
            
            if ($_FILES['group_image']['size'] > $maxFileSize) {
                $error_msg = "A kép túl nagy! A maximális megengedett méret 5MB.";
            } else {
                $tmpPath = $_FILES['group_image']['tmp_name'];
                $fileName = $_FILES['group_image']['name'];
                
                // Fájlnév tisztítása és egyedivé tétele
                $safeFileName = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $fileName);
                $destAbs = $uploadDirAbs . $safeFileName;

                if (move_uploaded_file($tmpPath, $destAbs)) {
                    // --- KÉP OPTIMALIZÁLÁSA ---
                    // Csak akkor hívjuk meg a Tinify-t, ha a fájl sikeresen átkerült a végleges helyére
                    optimizeImageWithTinify($destAbs);
                    
                    $group_image = $safeFileName;
                } else {
                    $error_msg = "Nem sikerült elmenteni a képet a szerveren.";
                }
            }
        }

        // --- Adatbázis mentés ---
        // Csak akkor hajtjuk végre, ha a validálás során (név, méret, feltöltés) nem keletkezett hiba
        if (empty($error_msg)) {
            $stmt = $conn->prepare("INSERT INTO groups (group_name, group_description, group_image, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $group_name, $group_description, $group_image);

            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                // Siker esetén átirányítás az új csoport oldalára
                header("Location: " . BASE_URL . "/pages/forum_group.php?group=$new_id");
                exit();
            } else {
                $error_msg = "Hiba történt a mentés során: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hozz létre saját tech közösségi csoportot a Techoázison és építs aktív közösséget egy témakör köré.">
    <title>Techoázis | Új csoport létrehozása</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>

    <style>
        .edit-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: var(--surface); border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark); }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; background-color: var(--dark-surface-alt); color: var(--text-light); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 1rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        textarea.form-control { min-height: 150px; resize: vertical; }
        .btn-submit-style {
            background: var(--primary-500, #2563eb);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit-style:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

    <div class="container section-padding">
        <div class="edit-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2><i class="fas fa-plus-circle"></i> Új csoport létrehozása</h2>
                <a href="<?= BASE_URL ?>/pages/forum.php" class="btn-back" style="text-decoration: none; color: var(--text-muted);">
                    <i class="fas fa-arrow-left"></i> Vissza a fórumhoz
                </a>
            </div>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/pages/create_group.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="group_name">Csoport neve</label>
                    <input type="text" id="group_name" name="group_name" class="form-control" 
                           placeholder="Pl. AI és Jövőkutatás" required>
                </div>

                <div class="form-group">
                    <label for="group_description">Csoport leírása</label>
                    <textarea id="group_description" name="group_description" class="form-control" 
                              placeholder="Miről szól ez a közösség?"></textarea>
                </div>

                <div class="form-group">
                    <label for="group_image">Csoport borítóképe (opcionális)</label>
                    <small style="color: var(--text-muted); display: block; margin-bottom: 5px;">Maximum méret: 5MB</small>
                    <input type="file" id="group_image" name="group_image" class="form-control" accept="image/*">
                </div>

                <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
                    <button type="submit" class="btn-submit-style">
                        <i class="fas fa-upload"></i>
                        Csoport létrehozása
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>