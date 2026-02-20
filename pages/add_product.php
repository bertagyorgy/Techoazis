<?php
// 1. Config betöltése relatív úton
require_once __DIR__ . '/../core/config.php';

// 2. Háttérlogika betöltése a már beállított ROOT_PATH segítségével
require_once ROOT_PATH . '/actions/add_product_logic.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Adj fel hardver hirdetést percek alatt a Techoázison! Tölts fel képeket, állíts be árat és találd meg a vevődet gyorsan.">
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
            pointer-events: none;
        }
        .badge-primary { background: #2563eb; }
        .badge-new { background: #10b981; }
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

            <form action="<?= BASE_URL ?>/pages/add_product.php" method="POST" enctype="multipart/form-data" id="productForm">
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
                    <label for="postImages">Termék képek (max 3 kép, egyenként max 5MB)</label>
                    <input type="file" id="postImages" class="form-control" accept="image/*" multiple>
                    <input type="file" name="images[]" id="hiddenImages" style="display:none" multiple>
                </div>
                <div id="imagePreview"></div>

                <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
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
    const MAX_FILES = 3;
    const MAX_SIZE = 5 * 1024 * 1024; // 5 MB

    const fileInput = document.getElementById('postImages');
    const hiddenInput = document.getElementById('hiddenImages');
    const previewContainer = document.getElementById('imagePreview');
    const form = document.getElementById('productForm');

    fileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        files.forEach(file => {
            if (selectedFiles.length >= MAX_FILES) return;
            
            // Méret ellenőrzés
            if (file.size > MAX_SIZE) {
                alert(`A(z) ${file.name} fájl túl nagy! Maximum 5MB engedélyezett.`);
                return;
            }

            // Duplikáció szűrés
            const isDuplicate = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!isDuplicate) {
                selectedFiles.push(file);
            }
        });

        renderPreviews();
        fileInput.value = ''; // Reset
    });

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
                badge.textContent = 'Galéria';
                badge.className = 'badge-status badge-new';
            }
        });
    }

    function removeImage(index) {
        selectedFiles.splice(index, 1);
        renderPreviews();
    }

    function renderPreviews() {
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
                `;
                previewContainer.appendChild(div);
                updateBadges();
            }
            reader.readAsDataURL(file);
        });
        
        // Input letiltása ha elértük a limitet
        fileInput.disabled = selectedFiles.length >= MAX_FILES;
        fileInput.parentElement.style.opacity = selectedFiles.length >= MAX_FILES ? '0.5' : '1';
    }

    form.addEventListener('submit', function(e) {
        // A kiválasztott fájlok átadása a rejtett inputnak
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        hiddenInput.files = dataTransfer.files;
    });
</script>
</html>
<?php 
// Kapcsolat lezárása a legvégén
if (isset($conn)) {
    $conn->close(); 
}
?>