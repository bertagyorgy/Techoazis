<?php
// product_detail.php
session_start();
require_once __DIR__ . '/config.php';

require_once ROOT_PATH . '/app/db.php';
$product_id = $_GET['id'] ?? 0;

// Termék adatok lekérése
$sql = "SELECT p.*, u.username as seller_username, u.profile_image as seller_avatar, u.username_slug AS user_slug,
               u.email as seller_email, u.user_id as seller_id
        FROM products p
        JOIN users u ON p.seller_user_id = u.user_id
        WHERE p.product_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit();
}

// Termék képek lekérése - sorrend a feltöltés szerint (vagy is_primary szerint)
$sql = "SELECT image_path FROM images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, image_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$images_result = $stmt->get_result();
$images = $images_result->fetch_all(MYSQLI_ASSOC);

// Debug: ezt élesben majd vedd ki, ha zavar
// echo "Keresett termék ID: " . $product_id . "<br>";
// echo "Talált képek száma: " . count($images) . "<br>";

// Ha nincs kép, alapértelmezettet használunk
if (empty($images)) {
    // Csak a relatív útvonalat adjuk meg, perjel nélkül az elején
    $images = [['image_path' => 'uploads/products/default_product.png']];
}

// Hasonló termékek lekérése
$sql = "SELECT p.*, u.username as seller_username,
               (SELECT image_path FROM images WHERE product_id = p.product_id ORDER BY is_primary DESC LIMIT 1) as main_image
        FROM products p
        JOIN users u ON p.seller_user_id = u.user_id
        WHERE p.category = ? 
          AND p.product_id != ?           -- Kizárjuk az aktuális terméket
          AND p.product_status = 'active'
        ORDER BY p.created_at DESC
        LIMIT 4";

$stmt = $conn->prepare($sql);
// Csak a kategóriát és az aktuális ID-t adjuk át
$stmt->bind_param('si', $product['category'], $product_id); 
$stmt->execute();
$similar_result = $stmt->get_result();
$similar_products = $similar_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | <?php echo htmlspecialchars($product['product_name']); ?></title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/product_detail_style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>

</head>
<body>
    <?php include ROOT_PATH . '/views/navbar.php'; ?>
    
    <section class="section-padding">
        <div class="product-detail-container">
            
            <div style="margin-bottom: 2rem; color: var(--text-light);">
                <a href="<?= BASE_URL ?>/shop.php" style="color: var(--accent-600); text-decoration: none;">Termékek</a>
                <span> / </span>
                <span><?php echo htmlspecialchars($product['category']); ?></span>
                <span> / </span>
                <span><?php echo htmlspecialchars($product['product_name']); ?></span>
            </div>
            
            <div class="product-detail-layout">
                
                <div class="product-gallery">
                    <div class="main-image-container">
                        <img id="main-product-image" 
                            src="<?= BASE_URL . '/' . ltrim(htmlspecialchars($images[0]['image_path']), '/') ?>"
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             class="main-image">
                        
                        <div class="image-status-badge <?php echo 'status-' . $product['product_status']; ?>">
                            <?php 
                            $status_text = [
                                'active' => 'Aktív',
                                'sold' => 'Eladva',
                                'hidden' => 'Rejtett'
                            ];
                            echo $status_text[$product['product_status']] ?? $product['product_status'];
                            ?>
                        </div>
                    </div>
                    
                    <?php if (count($images) > 1): ?>
                    <div class="thumbnails-container">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                            data-image="<?php echo htmlspecialchars(ltrim($image['image_path'], '/')); ?>"
                            onclick="changeMainImage('<?php echo htmlspecialchars(ltrim($image['image_path'], '/')); ?>', this)">
                            <img src="<?php echo BASE_URL . '/' . ltrim(htmlspecialchars($image['image_path']), '/'); ?>" 
                                alt="Termék kép <?php echo $index + 1; ?>">
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="gallery-meta-panel">
                    <div class="product-header">
                        <div class="product-category">
                            <i class="fas fa-tag"></i>
                            <?php echo htmlspecialchars($product['category']); ?>
                        </div>
                        
                        <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                        
                        <div class="product-price <?php echo $product['price'] ? '' : 'none'; ?>">
                            <?php 
                            if ($product['price']) {
                                echo number_format($product['price'], 0, ',', ' ') . ' Ft';
                            } else {
                                echo 'Alkuképes';
                            }
                            ?>
                        </div>
                    </div>
                    <?php if ($product['pickup_location']): ?>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Átvételi hely:</strong>
                            <div><?php echo htmlspecialchars($product['pickup_location']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <strong>Feladva:</strong>
                            <div><?php echo date('Y. m. d. H:i', strtotime($product['created_at'])); ?></div>
                        </div>
                    </div>

                    <?php if ($product['updated_at']): ?>
                    <div class="meta-item">
                        <i class="fas fa-sync"></i>
                        <div>
                            <strong>Utoljára frissítve:</strong>
                            <div><?php echo date('Y. m. d. H:i', strtotime($product['updated_at'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="product-meta">
                        <div class="product-description">
                            <h3>Termék leírása</h3>
                            <div class="product-description-content">
                                <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="product-info-sidebar">  
                        <div class="seller-card">
                            <div class="seller-header">
                                <div class="seller-avatar">
                                    <img src="<?php echo htmlspecialchars(BASE_URL . "/" . ($product['seller_avatar'] ?? 'images/default_avatar.png')); ?>" 
                                        alt="<?php echo htmlspecialchars($product['seller_username']); ?>">
                                </div>
                                <div class="seller-info">
                                    <h4><?php echo htmlspecialchars($product['seller_username']); ?></h4>
                                    <p>Eladó</p>
                                </div>
                            </div>
                            
                            <div class="seller-stats">
                                <div class="stat-item">
                                    <div class="stat-value">12</div>
                                    <div class="stat-label">Termék</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">4.8</div>
                                    <div class="stat-label">Értékelés</div>
                                </div>
                            </div>
                            
                            <?php
                            $slug = $product['user_slug'] ?? '';
                            ?>
                            <?php if ($slug !== ''): ?>
                            <div style="text-align: center; margin-top: 1rem;">
                                <a href="<?= BASE_URL ?>/profile?u=<?= urlencode($slug) ?>" class="btn-back">
                                    <i class="fas fa-user-circle"></i>
                                    Profil megtekintése
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions-detail">
                            <a href="<?= BASE_URL ?>/shop.php" class="btn-back">
                                <i class="fas fa-arrow-left"></i>
                                Vissza a termékekhez
                            </a>
                            
                            <?php if ($product['product_status'] === 'active'): ?>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if ($product['seller_id'] != $_SESSION['user_id']): ?>
                                        <a href="<?= BASE_URL ?>/conversation.php?product_id=<?php echo $product_id; ?>" 
                                        class="btn-message-seller">
                                            <i class="fas fa-comment-dots"></i>
                                            Üzenet küldése az eladónak
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/edit_product.php?id=<?php echo $product_id; ?>" 
                                        class="btn-message-seller" style="background: var(--primary-500);">
                                            <i class="fas fa-edit"></i>
                                            Termék szerkesztése
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/views/login.php" 
                                    class="btn-message-seller">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Jelentkezz be az üzenethez
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn-message-seller disabled" disabled>
                                    <i class="fas fa-times-circle"></i>
                                    Termék már nem elérhető
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>                
                </div>
            </div>
            
            <?php if (!empty($similar_products)): ?>
            <div class="similar-products">
                <h2 class="section-title">Hasonló termékek</h2>
                <div class="similar-grid">
                    <?php foreach ($similar_products as $product): // $similar helyett legyen $product! ?>
                        <?php 
                        // Itt előkészítjük a képet a kártya számára
                        $product['main_image'] = $product['main_image'] ?? 'uploads/products/default_product.png';
                        
                        // Most már a product_card.php az aktuális "hasonló" terméket fogja használni
                        include ROOT_PATH . '/product_card.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </section>
    
    <script>
        // Biztosítjuk, hogy ne legyen dupla perjel (rtrim)
        const baseUrl = '<?= rtrim(BASE_URL, '/') ?>'; 
        
        // PHP-ból átvett képlista (csak a relatív utak)
        const productImages = <?php echo json_encode(array_column($images, 'image_path')); ?>;

        // Képváltó funkció
        function changeMainImage(imageSrc, thumbnailElement) {
            // Ha a path relatív (nincs előtte perjel), akkor rakunk elé
            // Ha már van, nem rakunk.
            const cleanPath = imageSrc.startsWith('/') ? imageSrc : '/' + imageSrc;
            
            document.getElementById('main-product-image').src = baseUrl + cleanPath;
            
            // Thumbnail aktív állapot frissítése
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnailElement.classList.add('active');
        }
        
        let currentImageIndex = 0;
        function autoRotateImages() {
            if (productImages.length > 1) {
                currentImageIndex = (currentImageIndex + 1) % productImages.length;
                const nextImage = productImages[currentImageIndex];
                const nextThumb = document.querySelectorAll('.thumbnail')[currentImageIndex];
                
                // Mivel a productImages tömbben nyers stringek vannak (pl "uploads/.."),
                // a changeMainImage ezt majd korrigálja a perjellel.
                changeMainImage(nextImage, nextThumb);
            }
        }
        
        // Eseményfigyelők
        const gallery = document.querySelector('.product-gallery');
        if(gallery){
            gallery.addEventListener('mouseenter', function() {
                // clearInterval(rotationInterval); // Ha használnál automatikus váltást
            });
        }
    </script>
</body>
</html>