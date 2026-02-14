<?php
// add_product.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';


// Csak bejelentkezett felhasználók tölthetnek fel terméket
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Form beküldés kezelése (Insert)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = (int)($_POST['price'] ?? 0);
    $pickup_location = $_POST['pickup_location'] ?? '';
    $product_status = 'active'; // Új terméknél alapértelmezett
    $description = $_POST['product_description'] ?? '';

    // Egyszerű validálás
    if (empty($product_name) || empty($category) || $price <= 0) {
        $error_msg = "A név, kategória és egy érvényes ár megadása kötelező!";
    } else {
        // 1. Termék mentése
        $insert_sql = "INSERT INTO products (product_name, category, price, pickup_location, product_status, product_description, seller_user_id, created_at, updated_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('ssisssi', $product_name, $category, $price, $pickup_location, $product_status, $description, $user_id);

        if ($stmt->execute()) {
            $product_id = $conn->insert_id; 
            
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = ROOT_PATH . '/uploads/products/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $img_sql = "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)";
                $img_stmt = $conn->prepare($img_sql);

                $upload_index = 0; // Manuális számláló a sorrendhez
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                        $file_name = time() . '_' . $upload_index . '_' . uniqid() . '.' . $file_ext;
                        $target_file = $upload_dir . $file_name;
                        $db_path = 'uploads/products/' . $file_name;

                        if (move_uploaded_file($tmp_name, $target_file)) {
                            // Az abszolút első sikeresen feltöltött kép a borítókép
                            $is_primary = ($upload_index === 0) ? 1 : 0;
                            $sort_order = $upload_index + 1;

                            $img_stmt->bind_param('isii', $product_id, $db_path, $is_primary, $sort_order);
                            $img_stmt->execute();
                            $upload_index++; // Csak sikeres mentés után növeljük
                        }
                    }
                }
                $img_stmt->close();
            }

            header("Location: " . BASE_URL . "/pages/product_detail.php?id=$product_id&msg=success");
            exit(); 
        } else {
            $error_msg = "Hiba: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | Új termék feltöltése</title>
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
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>

    <style>
        .edit-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: var(--surface); border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark); }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; background-color: var(--dark-surface-alt); color: var(--text-light); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 1rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        textarea.form-control { min-height: 150px; resize: vertical; }
        .file-inputs { position: relative; }
        .file-inputs label{ background: var(--dark-bg); padding: 10px 15px; border-radius: 8px; color: white; font-size: 1rem; }
        .file-inputs input[type="file"] { width: 100%; padding: 0.75rem; border: 2px dashed var(--border-color); border-radius: var(--border-radius-md); cursor: pointer; background: var(--surface); display: none; }
        #imagePreview { display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
        .preview-item { position: relative; width: 100px; height: 100px; }
        .preview-thumb { width: 100%; height: 100%; object-fit: scale-down; border-radius: var(--border-radius-md); border: 2px solid var(--primary-300); }
        .remove-image { position: absolute; top: -8px; right: -8px; width: 28px; height: 28px; border-radius: 50%; background: var(--danger); color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--transition-fast); }
        .remove-image:hover { background: #b91c1c; transform: scale(1.1); }
        .btn-submit-style { background: var(--primary-500, #2563eb); color: white; padding: 12px 32px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; transition: all 0.3s ease; }
        .btn-submit-style:hover { filter: brightness(1.1); transform: translateY(-2px); }
        /* Badge alapstílusok */
        .badge-status {
            flex-direction: column;
            position: absolute;
            bottom: 5px;
            left: 5px;
            right: 5px;
            padding: 2px 5px;
            font-size: 10px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            color: white;
            pointer-events: none; /* Ne zavarja a kattintást */
        }
        .badge-primary { background: #2563eb; } /* Kék a borítóképnek */
        .badge-new { background: #10b981; }     /* Zöld az újnak */
        .hidden-upload { display: none !important; }
    </style>
</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

    <div class="container section-padding">
        <div class="edit-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2><i class="fas fa-plus-circle"></i> Új termék hirdetése</h2>
                <a href="<?= BASE_URL ?>/pages/shop.php" class="btn-back" style="text-decoration: none; color: var(--text-muted);">
                    <i class="fas fa-arrow-left"></i> Vissza a shopba
                </a>
            </div>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/pages/add_product.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Termék neve</label>
                    <input type="text" id="product_name" name="product_name" class="form-control" 
                           placeholder="Pl. Nvidia RTX 3080 Videókártya" required>
                </div>

                <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="category">Kategória</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="" disabled selected>Válassz kategóriát...</option>
                            <option value="Adattárolók">Adattárolók</option>
                            <option value="Alkatrészek">Alkatrészek</option>
                            <option value="Audio technika">Audio technika</option>
                            <option value="Autós elektronika">Autós elektronika</option>
                            <option value="Drónok">Drónok</option>
                            <option value="Elektromos rollerek">Elektromos rollerek</option>
                            <option value="Fejhallgatók">Fejhallgatók</option>
                            <option value="Fejhallgatók">Fülhallgatók</option>
                            <option value="Fényképezőgépek">Fényképezőgépek</option>
                            <option value="Gaming">Gaming</option>
                            <option value="GPS & Navigáció">GPS & Navigáció</option>
                            <option value="Hálózati eszközök">Hálózati eszközök</option>
                            <option value="Hangfalak">Hangfalak</option>
                            <option value="Hangtechnika">Hangtechnika</option>
                            <option value="Hardver">Hardver</option>
                            <option value="Háztartási kisgépek">Háztartási kisgépek</option>
                            <option value="Hordozható hangszórók">Hordozható hangszórók</option>
                            <option value="Ipari elektronika">Ipari elektronika</option>
                            <option value="Játékkonzolok">Játékkonzolok</option>
                            <option value="Kábelek és adapterek">Kábelek és adapterek</option>
                            <option value="Kamerák">Kamerák</option>
                            <option value="Kiegészítők">Kiegészítők</option>
                            <option value="Kivetítők">Kivetítők</option>
                            <option value="Laptopok">Laptopok</option>
                            <option value="Megfigyelő rendszerek">Megfigyelő rendszerek</option>
                            <option value="Mikrofonok">Mikrofonok</option>
                            <option value="Mobiltelefonok">Mobiltelefonok</option>
                            <option value="Monitorok">Monitorok</option>
                            <option value="Nyomtatók és scannerek">Nyomtatók és scannerek</option>
                            <option value="Okosóra">Okosóra</option>
                            <option value="Okosotthon eszközök">Okosotthon eszközök</option>
                            <option value="PC konfigurációk">PC konfigurációk</option>
                            <option value="Periféria">Periféria</option>
                            <option value="Szoftverek">Szoftverek</option>
                            <option value="Szünetmentes tápegységek">Szünetmentes tápegységek</option>
                            <option value="Tabletek">Tabletek</option>
                            <option value="Tápellátás">Tápellátás</option>
                            <option value="Televíziók">Televíziók</option>
                            <option value="Videókártyák">Videókártyák</option>
                            <option value="Zenelejátszók">Zenelejátszók</option>
                            <option value="Egyéb">Egyéb</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Ár (Ft)</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               placeholder="0" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pickup_location">Átvétel helye</label>
                    <input type="text" id="pickup_location" name="pickup_location" class="form-control" 
                           placeholder="Pl. Budapest, XI. kerület">
                </div>

                <div class="form-group">
                    <label for="product_description">Részletes leírás</label>
                    <textarea id="product_description" name="product_description" class="form-control" 
                              placeholder="Írd le a termék állapotát, garanciát, stb..."></textarea>
                </div>

                <div class="form-group">
                    <label for="postImages">Termék képek (max 3 kép, az első lesz a borítókép)</label>
                    <input type="file" id="postImages" name="images[]" class="form-control" accept="image/*" multiple>
                </div>
                <div id="imagePreview"></div>

                <div style="margin-top: 2rem; border-top: 1px solid #eee; pt-2rem; padding-top: 1.5rem;">
                    <button type="submit" class="btn-submit-style">
                        <i class="fas fa-upload"></i>
                        Termék feltöltése
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
<script>
let selectedFiles = [];

// --- EZ AZ ÚJ RÉSZ: Figyeljük a form beküldését ---
document.querySelector('form').addEventListener('submit', function(e) {
    const input = document.getElementById('postImages');
    const dt = new DataTransfer();
    
    // A beküldés előtt belepakoljuk a tömb tartalmát az inputba
    selectedFiles.forEach(file => dt.items.add(file));
    input.files = dt.files;
    
    // Most már engedhetjük a formot a PHP-nak, benne lesznek a képek!
});
// ------------------------------------------------

function updateBadges() {
    const cards = document.querySelectorAll('#imagePreview .preview-item');
    cards.forEach((card, index) => {
        let badge = card.querySelector('.badge-status');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge-status';
            card.appendChild(badge);
        }
        if (index === 0) {
            badge.textContent = 'Borítókép';
            badge.className = 'badge-status badge-primary';
        } else {
            badge.textContent = 'Új feltöltés';
            badge.className = 'badge-status badge-new';
        }
    });
}

function updateUploadVisibility() {
    const inputField = document.getElementById('postImages');
    if (selectedFiles.length >= 3) {
        inputField.parentElement.style.opacity = '0.5';
        inputField.disabled = true;
    } else {
        inputField.parentElement.style.opacity = '1';
        inputField.disabled = false;
    }
    updateBadges();
}

function removeImage(index) {
    selectedFiles.splice(index, 1);
    renderPreviews(); // Itt elég csak újrarenderelni
}

function renderPreviews() {
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" class="preview-thumb">
                <button type="button" class="remove-image" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
                <span class="badge-status"></span>
            `;
            previewContainer.appendChild(div);
            updateUploadVisibility();
        }
        reader.readAsDataURL(file);
    });

    if (selectedFiles.length === 0) updateUploadVisibility();
}

document.getElementById('postImages').addEventListener('change', function(e) {
    const newFiles = Array.from(this.files);
    
    newFiles.forEach(file => {
        if (selectedFiles.length < 3) {
            const isDuplicate = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!isDuplicate) {
                selectedFiles.push(file);
            }
        }
    });

    renderPreviews();
    
    // Ez most már maradhat! Kiürítjük, hogy ne legyen duplázódás a köv. tallózásnál.
    e.target.value = ''; 
});
</script>
</html>