<?php
// edit_product.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$product_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM products WHERE product_id = ? AND seller_user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: " . BASE_URL . "/pages/shop.php");
    exit();
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = (int)($_POST['price'] ?? 0);
    $pickup_location = $_POST['pickup_location'] ?? '';
    $product_status = $_POST['product_status'] ?? 'active';
    $description = $_POST['product_description'] ?? '';

    if (empty($product_name) || empty($category)) {
        $error_msg = "A név és a kategória megadása kötelező!";
    } else {
        $update_sql = "UPDATE products SET 
                        product_name = ?, 
                        category = ?, 
                        price = ?, 
                        pickup_location = ?, 
                        product_status = ?, 
                        product_description = ?, 
                        updated_at = NOW() 
                       WHERE product_id = ? AND seller_user_id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('ssisssii', 
            $product_name, $category, $price, $pickup_location, 
            $product_status, $description, $product_id, $user_id
        );

        if ($update_stmt->execute()) {
            if (!empty($_POST['removed_images'])) {
                foreach ($_POST['removed_images'] as $img_id) {
                    $img_id = (int)$img_id;
                    $path_sql = "SELECT image_path FROM product_images WHERE image_id = ? AND product_id = ?";
                    $p_stmt = $conn->prepare($path_sql);
                    $p_stmt->bind_param('ii', $img_id, $product_id);
                    $p_stmt->execute();
                    $p_res = $p_stmt->get_result();
                    
                    if ($row = $p_res->fetch_assoc()) {
                        $full_path = ROOT_PATH . '/' . $row['image_path'];
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                    $del_sql = "DELETE FROM product_images WHERE image_id = ? AND product_id = ?";
                    $d_stmt = $conn->prepare($del_sql);
                    $d_stmt->bind_param('ii', $img_id, $product_id);
                    $d_stmt->execute();
                }
            }

            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = ROOT_PATH . '/uploads/products/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $count_sql = "SELECT COUNT(*) as total FROM product_images WHERE product_id = ?";
                $c_stmt = $conn->prepare($count_sql);
                $c_stmt->bind_param('i', $product_id);
                $c_stmt->execute();
                $current_count = $c_stmt->get_result()->fetch_assoc()['total'];

                $img_sql = "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)";
                $img_stmt = $conn->prepare($img_sql);

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                        $file_name = time() . '_' . $key . '_' . uniqid() . '.' . $file_ext;
                        $target_file = $upload_dir . $file_name;
                        $db_path = 'uploads/products/' . $file_name;

                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $is_primary = ($current_count == 0 && $key === 0) ? 1 : 0;
                            $sort_order = $current_count + $key + 1;
                            $img_stmt->bind_param('isii', $product_id, $db_path, $is_primary, $sort_order);
                            $img_stmt->execute();
                        }
                    }
                }
                $img_stmt->close();
            }
            $success_msg = "Termék és képek sikeresen frissítve!";
            $product['product_name'] = $product_name;
            $product['category'] = $category;
            $product['price'] = $price;
            $product['pickup_location'] = $pickup_location;
            $product['product_status'] = $product_status;
            $product['product_description'] = $description;
        } else {
            $error_msg = "Hiba történt a mentés során: " . $conn->error;
        }
    }
}

$count_query = "SELECT COUNT(*) as total FROM product_images WHERE product_id = ?";
$c_stmt = $conn->prepare($count_query);
$c_stmt->bind_param('i', $product_id);
$c_stmt->execute();
$current_image_count = $c_stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Módosítsd hirdetésed adatait, képeit és árát a Techoázison, hogy még több érdeklődőt érj el.">
    <title>Termék szerkesztése - <?php echo htmlspecialchars($product['product_name']); ?></title>
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
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
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

        .image-management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, 120px);
            gap: 15px;
            margin-top: 10px;
        }

        .image-card {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #eee;
            background: #f8f9fa;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .image-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .managed-image {
            width: 100%;
            height: 100%;
            object-fit: scale-down;
        }

        .btn-remove-overlay {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.9);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            z-index: 10;
            transition: transform 0.2s;
        }
        .btn-remove-overlay:hover { transform: scale(1.1); background: #b91c1c; }

        .badge-status {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            color: white;
            font-size: 10px;
            text-align: center;
            padding: 2px 0;
            font-weight: 600;
        }
        .badge-primary { background: rgba(37, 99, 235, 0.85); }
        .badge-new { background: rgba(16, 185, 129, 0.85); }

        .upload-card-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s;
            background: #fafafa;
        }

        .upload-card-label:hover {
            border-color: var(--primary-500);
            color: var(--primary-500);
            background: #f0f7ff;
        }

        .upload-card-label i { font-size: 1.5rem; margin-bottom: 5px; }
        .upload-card-label span { font-size: 11px; font-weight: 600; text-align: center; }
        
        .hidden-upload { display: none !important; }
    </style>
</head>
<body>
    <?php include ROOT_PATH . '/views/navbar.php'; ?>

    <div class="container section-padding">
        <div class="edit-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2><i class="fas fa-edit"></i> Termék szerkesztése</h2>
                <a href="<?= BASE_URL ?>/pages/product_detail.php?id=<?php echo $product_id; ?>" class="btn-back">
                    <i class="fas fa-eye"></i> Megtekintés
                </a>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Termék neve</label>
                    <input type="text" id="product_name" name="product_name" class="form-control" 
                           value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>

                <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="category">Kategória</label>
                        <select id="category" name="category" class="form-control">
                            <option value="Adattárolók" <?php echo ($product['category'] ?? '') == 'Adattárolók' ? 'selected' : ''; ?>>Adattárolók</option>
                            <option value="Alkatrészek" <?php echo ($product['category'] ?? '') == 'Alkatrészek' ? 'selected' : ''; ?>>Alkatrészek</option>
                            <option value="Audio technika" <?php echo ($product['category'] ?? '') == 'Audio technika' ? 'selected' : ''; ?>>Audio technika</option>
                            <option value="Autós elektronika" <?php echo ($product['category'] ?? '') == 'Autós elektronika' ? 'selected' : ''; ?>>Autós elektronika</option>
                            <option value="Drónok" <?php echo ($product['category'] ?? '') == 'Drónok' ? 'selected' : ''; ?>>Drónok</option>
                            <option value="Elektromos rollerek" <?php echo ($product['category'] ?? '') == 'Elektromos rollerek' ? 'selected' : ''; ?>>Elektromos rollerek</option>
                            <option value="Fejhallgatók" <?php echo ($product['category'] ?? '') == 'Fejhallgatók' ? 'selected' : ''; ?>>Fejhallgatók</option>
                            <option value="Fülhallgatók" <?php echo ($product['category'] ?? '') == 'Fülhallgatók' ? 'selected' : ''; ?>>Fülhallgatók</option>
                            <option value="Fényképezőgépek" <?php echo ($product['category'] ?? '') == 'Fényképezőgépek' ? 'selected' : ''; ?>>Fényképezőgépek</option>
                            <option value="Gaming" <?php echo ($product['category'] ?? '') == 'Gaming' ? 'selected' : ''; ?>>Gaming</option>
                            <option value="GPS & Navigáció" <?php echo ($product['category'] ?? '') == 'GPS & Navigáció' ? 'selected' : ''; ?>>GPS & Navigáció</option>
                            <option value="Hálózati eszközök" <?php echo ($product['category'] ?? '') == 'Hálózati eszközök' ? 'selected' : ''; ?>>Hálózati eszközök</option>
                            <option value="Hangfalak" <?php echo ($product['category'] ?? '') == 'Hangfalak' ? 'selected' : ''; ?>>Hangfalak</option>
                            <option value="Hangtechnika" <?php echo ($product['category'] ?? '') == 'Hangtechnika' ? 'selected' : ''; ?>>Hangtechnika</option>
                            <option value="Hardver" <?php echo ($product['category'] ?? '') == 'Hardver' ? 'selected' : ''; ?>>Hardver</option>
                            <option value="Háztartási kisgépek" <?php echo ($product['category'] ?? '') == 'Háztartási kisgépek' ? 'selected' : ''; ?>>Háztartási kisgépek</option>
                            <option value="Hordozható hangszórók" <?php echo ($product['category'] ?? '') == 'Hordozható hangszórók' ? 'selected' : ''; ?>>Hordozható hangszórók</option>
                            <option value="Ipari elektronika" <?php echo ($product['category'] ?? '') == 'Ipari elektronika' ? 'selected' : ''; ?>>Ipari elektronika</option>
                            <option value="Játékkonzolok" <?php echo ($product['category'] ?? '') == 'Játékkonzolok' ? 'selected' : ''; ?>>Játékkonzolok</option>
                            <option value="Kábelek és adapterek" <?php echo ($product['category'] ?? '') == 'Kábelek és adapterek' ? 'selected' : ''; ?>>Kábelek és adapterek</option>
                            <option value="Kamerák" <?php echo ($product['category'] ?? '') == 'Kamerák' ? 'selected' : ''; ?>>Kamerák</option>
                            <option value="Kiegészítők" <?php echo ($product['category'] ?? '') == 'Kiegészítők' ? 'selected' : ''; ?>>Kiegészítők</option>
                            <option value="Kivetítők" <?php echo ($product['category'] ?? '') == 'Kivetítők' ? 'selected' : ''; ?>>Kivetítők</option>
                            <option value="Laptopok" <?php echo ($product['category'] ?? '') == 'Laptopok' ? 'selected' : ''; ?>>Laptopok</option>
                            <option value="Megfigyelő rendszerek" <?php echo ($product['category'] ?? '') == 'Megfigyelő rendszerek' ? 'selected' : ''; ?>>Megfigyelő rendszerek</option>
                            <option value="Mikrofonok" <?php echo ($product['category'] ?? '') == 'Mikrofonok' ? 'selected' : ''; ?>>Mikrofonok</option>
                            <option value="Mobiltelefonok" <?php echo ($product['category'] ?? '') == 'Mobiltelefonok' ? 'selected' : ''; ?>>Mobiltelefonok</option>
                            <option value="Monitorok" <?php echo ($product['category'] ?? '') == 'Monitorok' ? 'selected' : ''; ?>>Monitorok</option>
                            <option value="Nyomtatók és scannerek" <?php echo ($product['category'] ?? '') == 'Nyomtatók és scannerek' ? 'selected' : ''; ?>>Nyomtatók és scannerek</option>
                            <option value="Okosóra" <?php echo ($product['category'] ?? '') == 'Okosóra' ? 'selected' : ''; ?>>Okosóra</option>
                            <option value="Okosotthon eszközök" <?php echo ($product['category'] ?? '') == 'Okosotthon eszközök' ? 'selected' : ''; ?>>Okosotthon eszközök</option>
                            <option value="PC konfigurációk" <?php echo ($product['category'] ?? '') == 'PC konfigurációk' ? 'selected' : ''; ?>>PC konfigurációk</option>
                            <option value="Periféria" <?php echo ($product['category'] ?? '') == 'Periféria' ? 'selected' : ''; ?>>Periféria</option>
                            <option value="Szoftverek" <?php echo ($product['category'] ?? '') == 'Szoftverek' ? 'selected' : ''; ?>>Szoftverek</option>
                            <option value="Szünetmentes tápegységek" <?php echo ($product['category'] ?? '') == 'Szünetmentes tápegységek' ? 'selected' : ''; ?>>Szünetmentes tápegységek</option>
                            <option value="Tabletek" <?php echo ($product['category'] ?? '') == 'Tabletek' ? 'selected' : ''; ?>>Tabletek</option>
                            <option value="Tápellátás" <?php echo ($product['category'] ?? '') == 'Tápellátás' ? 'selected' : ''; ?>>Tápellátás</option>
                            <option value="Televíziók" <?php echo ($product['category'] ?? '') == 'Televíziók' ? 'selected' : ''; ?>>Televíziók</option>
                            <option value="Videókártyák" <?php echo ($product['category'] ?? '') == 'Videókártyák' ? 'selected' : ''; ?>>Videókártyák</option>
                            <option value="Zenelejátszók" <?php echo ($product['category'] ?? '') == 'Zenelejátszók' ? 'selected' : ''; ?>>Zenelejátszók</option>
                            <option value="Egyéb" <?php echo ($product['category'] ?? '') == 'Egyéb' ? 'selected' : ''; ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Ár (Ft)</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               value="<?php echo htmlspecialchars($product['price']); ?>">
                    </div>
                </div>

                <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="pickup_location">Átvétel helye</label>
                        <input type="text" id="pickup_location" name="pickup_location" class="form-control" 
                               value="<?php echo htmlspecialchars($product['pickup_location']); ?>" placeholder="Város, kerület...">
                    </div>
                    <div class="form-group">
                        <label for="product_status">Állapot</label>
                        <select id="product_status" name="product_status" class="form-control">
                            <option value="active" <?php echo $product['product_status'] == 'active' ? 'selected' : ''; ?>>Aktív (Listázva)</option>
                            <option value="sold" <?php echo $product['product_status'] == 'sold' ? 'selected' : ''; ?>>Eladva</option>
                            <option value="hidden" <?php echo $product['product_status'] == 'hidden' ? 'selected' : ''; ?>>Rejtett</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="product_description">Leírás</label>
                    <textarea id="product_description" name="product_description" class="form-control"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Termék képei (Max. 3)</label>
                    
                    <div class="image-management-grid">
                        <div id="existingImages" style="display: contents;">
                            <?php
                            $img_query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
                            $i_stmt = $conn->prepare($img_query);
                            $i_stmt->bind_param('i', $product_id);
                            $i_stmt->execute();
                            $imgs = $i_stmt->get_result();
                            while ($img = $imgs->fetch_assoc()):
                            ?>
                                <div class="image-card" id="img-container-<?= $img['image_id'] ?>">
                                    <img src="<?= BASE_URL . '/' . $img['image_path'] ?>" class="managed-image">
                                    <button type="button" class="btn-remove-overlay" onclick="removeExistingImage(<?= $img['image_id'] ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <span class="badge-status <?= $img['is_primary'] ? 'badge-primary' : '' ?>">
                                        <?= $img['is_primary'] ? 'Borítókép' : '' ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div id="imagePreview" style="display: contents;"></div>

                        <label for="postImages" id="uploadCard" class="upload-card-label <?= ($current_image_count >= 3) ? 'hidden-upload' : '' ?>">
                            <i class="fas fa-plus"></i>
                            <span>Új kép</span>
                        </label>
                        <input type="file" id="postImages" name="images[]" accept="image/*" multiple style="display: none;">
                    </div>
                    
                    <div id="removedImagesInputs"></div>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn-submit-style">
                        <i class="fas fa-save"></i> Változtatások mentése
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
// Globális tömb az új fájlok tárolására
let selectedFiles = [];

/**
 * Frissíti a jelvényeket (Borítókép / Új feltöltés) a sorrend alapján
 */
function updateBadges() {
    // Összeszedjük az összes kártyát: előbb a már meglévőket, aztán az újakat
    const allCards = document.querySelectorAll('#existingImages .image-card, #imagePreview .image-card');
    
    allCards.forEach((card, index) => {
        let badge = card.querySelector('.badge-status');
        
        // Ha valamiért nincs badge elem, létrehozzuk
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge-status';
            card.appendChild(badge);
        }

        if (index === 0) {
            // Mindig a legelső elem a borítókép
            badge.textContent = 'Borítókép';
            badge.className = 'badge-status badge-primary';
        } else {
            // A többi elemnél megnézzük, hogy új-e vagy régi
            const isNew = card.closest('#imagePreview') !== null;
            if (isNew) {
                badge.textContent = 'Új feltöltés';
                badge.className = 'badge-status badge-new';
            } else {
                // Régi kép, ami nem az első: ne legyen felirat
                badge.textContent = '';
                badge.className = 'badge-status'; 
            }
        }
    });
}

/**
 * Kezeli a feltöltő gomb láthatóságát és frissíti a jelvényeket
 */
function updateUploadButtonVisibility() {
    const uploadCard = document.getElementById('uploadCard');
    const existingCount = document.querySelectorAll('#existingImages .image-card').length;
    const previewCount = selectedFiles.length;
    
    // Frissítjük a sorrend alapú jelöléseket
    updateBadges();
    
    if ((existingCount + previewCount) >= 3) {
        uploadCard.classList.add('hidden-upload');
    } else {
        uploadCard.classList.remove('hidden-upload');
    }
}

/**
 * Meglévő (szerveroldali) kép eltávolítása
 */
function removeExistingImage(imageId) {
    if (confirm('Biztosan törlöd ezt a képet?')) {
        const container = document.getElementById('img-container-' + imageId);
        if (container) container.remove();
        
        // Elküldjük a PHP-nak a törlendő ID-t
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'removed_images[]';
        input.value = imageId;
        document.getElementById('removedImagesInputs').appendChild(input);
        
        updateUploadButtonVisibility();
    }
}

/**
 * Frissen kiválasztott kép eltávolítása a listából
 */
function removeNewImage(index) {
    selectedFiles.splice(index, 1);
    syncInputAndRender();
}

/**
 * Szinkronizálja a rejtett inputot a tömbbel és újraépíti a nézetet
 */
function syncInputAndRender() {
    const input = document.getElementById('postImages');
    const dt = new DataTransfer();
    
    selectedFiles.forEach(file => dt.items.add(file));
    input.files = dt.files; 

    renderPreviews();
}

/**
 * Az újonnan kiválasztott képek előnézetének generálása
 */
function renderPreviews() {
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(event) {
            const card = document.createElement('div');
            card.className = 'image-card';
            
            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'managed-image';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-remove-overlay';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = () => removeNewImage(index);
            
            // Üres badge, amit az updateBadges fog feltölteni
            const badge = document.createElement('span');
            badge.className = 'badge-status';
            
            card.appendChild(img);
            card.appendChild(removeBtn);
            card.appendChild(badge);
            previewContainer.appendChild(card);
            
            // Minden renderelés végén (vagy az utolsó fájlnál) frissítünk
            if (index === selectedFiles.length - 1) {
                updateUploadButtonVisibility();
            }
        }
        reader.readAsDataURL(file);
    });
    
    // Ha kiürült a lista, akkor is frissíteni kell
    if (selectedFiles.length === 0) {
        updateUploadButtonVisibility();
    }
}

/**
 * Input változás eseménykezelő
 */
document.getElementById('postImages').addEventListener('change', function(e) {
    const existingCount = document.querySelectorAll('#existingImages .image-card').length;
    const newFiles = Array.from(this.files);
    
    let rejectedCount = 0;
    
    newFiles.forEach(file => {
        const currentTotal = existingCount + selectedFiles.length;
        if (currentTotal < 3) {
            const isDuplicate = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!isDuplicate) {
                selectedFiles.push(file);
            }
        } else {
            rejectedCount++;
        }
    });

    syncInputAndRender();
    
    if (rejectedCount > 0) {
        alert("Maximum 3 képet tárolhatsz. Néhány kép nem került hozzáadásra.");
    }
});

// Oldalbetöltéskor is ellenőrizzük a badge-eket
document.addEventListener('DOMContentLoaded', updateBadges);
</script>
</body>
</html>