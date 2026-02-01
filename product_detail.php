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

// Termék képek lekérése
$sql = "SELECT image_path FROM images WHERE product_id = ? ORDER BY image_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$images_result = $stmt->get_result();
$images = $images_result->fetch_all(MYSQLI_ASSOC);

// Ha nincs kép, alapértelmezettet használunk
if (empty($images)) {
    $images = [['image_path' =>  BASE_URL . '/images/default_product.jpg']];
}

// Hasonló termékek (ugyanabban a kategóriában, de más eladótól)
$sql = "SELECT p.*, u.username as seller_username,
               (SELECT image_path FROM images WHERE product_id = p.product_id LIMIT 1) as main_image
        FROM products p
        JOIN users u ON p.seller_user_id = u.user_id
        WHERE p.category = ? 
          AND p.product_status = 'active'
          AND p.product_id != ?
          AND p.seller_user_id != ?
        ORDER BY p.created_at DESC
        LIMIT 4";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sii', $product['category'], $product_id, $product['seller_id']);
$stmt->execute();
$similar_result = $stmt->get_result();
$similar_products = $similar_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Techoázis</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/product_detail_style.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="<?= BASE_URL ?>/static/index.js" defer></script>
    <script src="<?= BASE_URL ?>/static/forum.js" defer></script>

</head>
<body>
    <?php include ROOT_PATH . '/views/navbar.php'; ?>
    
    <section class="section-padding">
        <div class="product-detail-container">
            
            <!-- Breadcrumb -->
            <div style="margin-bottom: 2rem; color: var(--text-light);">
                <a href="<?= BASE_URL ?>/shop.php" style="color: var(--accent-600); text-decoration: none;">Termékek</a>
                <span> / </span>
                <span><?php echo htmlspecialchars($product['category']); ?></span>
                <span> / </span>
                <span><?php echo htmlspecialchars($product['product_name']); ?></span>
            </div>
            
            <!-- Main Product Layout -->
            <div class="product-detail-layout">
                
                <!-- Left: Image Gallery -->
                <div class="product-gallery">
                    <div class="main-image-container">
                        <img id="main-product-image" 
                            src="<?= htmlspecialchars(BASE_URL . '/' . $images[0]['image_path']) ?>"
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
                    
                    <!-- Thumbnails -->
                    <?php if (count($images) > 1): ?>
                    <div class="thumbnails-container">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-image="<?php echo htmlspecialchars($image['image_path']); ?>"
                                 onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>', this)">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     alt="Termék kép <?php echo $index + 1; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- Pickup / dates under the images (left column) -->
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
                    <!-- Product Meta -->
                    <div class="product-meta">
                        <!-- Product Description -->
                        <div class="product-description">
                            <h3>Termék leírása</h3>
                            <div class="product-description-content">
                                <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Right: Product Info -->
                    <div class="product-info-sidebar">  
                        <!-- Seller Card -->
                        <div class="seller-card">
                            <div class="seller-header">
                                <div class="seller-avatar">
                                    
                                    <img src="<?php echo htmlspecialchars(BASE_URL . "/" . $product['seller_avatar']); ?>" 
                                        alt="<?php echo htmlspecialchars($product['seller_username']); ?>">
                                </div>
                                <div class="seller-info">
                                    <h4><?php echo htmlspecialchars($product['seller_username']); ?></h4>
                                    <p>Eladó</p>
                                </div>
                            </div>
                            
                            <!-- Seller Stats (még nincs implementálva, de placeholder) -->
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
                        
                        
                        
                        <!-- Action Buttons -->
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
            
            <!-- Similar Products -->
            <?php if (!empty($similar_products)): ?>
            <div class="similar-products">
                <h2 class="section-title">Hasonló termékek</h2>
                <div class="similar-grid">
                    <?php foreach ($similar_products as $similar): ?>
                        <?php 
                        $similar_card_data = [
                            'product_id' => $similar['product_id'],
                            'product_name' => $similar['product_name'],
                            'price' => $similar['price'],
                            'seller_username' => $similar['seller_username'],
                            'main_image' => $similar['main_image'] ?? 'images/default_product.jpg',
                            'category' => $similar['category'],
                            'product_status' => $similar['product_status'],
                            'created_at' => $similar['created_at']
                        ];
                        ?>
                        <?php include ROOT_PATH . '/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </section>
    
    <script>
        const baseUrl = '<?= BASE_URL ?>/';
        const images = <?php echo json_encode(array_column($images, 'image_path')); ?>;

        // Képváltó funkció
        function changeMainImage(imageSrc, thumbnailElement) {
            // Fő kép frissítése
            document.getElementById('main-product-image').src = baseUrl + imageSrc;
            
            // Thumbnail aktív állapot frissítése
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            
            thumbnailElement.classList.add('active');
        }
        
        // Automatikus képváltás (opcionális)
        let currentImageIndex = 0;
        const images = <?php echo json_encode(array_column($images, 'image_path')); ?>;
        
        function autoRotateImages() {
            if (images.length > 1) {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                changeMainImage(images[currentImageIndex], 
                    document.querySelectorAll('.thumbnail')[currentImageIndex]);
            }
        }
        
        // 5 másodpercenként vált (opcionális, ki lehet kapcsolni)
        // let rotationInterval = setInterval(autoRotateImages, 5000);
        
        // Ha rámegy az egér a galériára, állítsd meg a váltást
        document.querySelector('.product-gallery').addEventListener('mouseenter', function() {
            // clearInterval(rotationInterval);
        });
        
        document.querySelector('.product-gallery').addEventListener('mouseleave', function() {
            // rotationInterval = setInterval(autoRotateImages, 5000);
        });
    </script>
</body>
</html>