<?php
// product_detail.php
session_start();
require_once __DIR__ . '/app/db.php';

$product_id = $_GET['id'] ?? 0;

// Termék adatok lekérése
$sql = "SELECT p.*, u.username as seller_username, u.profile_image as seller_avatar, 
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
    header('Location: shop.php');
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
    $images = [['image_path' => 'images/default_product.jpg']];
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
    <title><?php echo htmlspecialchars($product['product_name']); ?> - TechOázis</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/utility_classes.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        /* ===============================
          PRODUCT DETAIL STYLES
        =============================== */
        .product-detail-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .product-detail-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }
        
        @media (max-width: 992px) {
            .product-detail-layout {
                grid-template-columns: 1fr;
            }
        }
        
        /* Image Gallery */
        .product-gallery {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .main-image-container {
            position: relative;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            background: var(--background);
            border: 1px solid var(--border-color);
            height: 400px;
        }
        
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 1rem;
        }
        
        .image-status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 2;
        }
        
        .status-active {
            background: var(--success);
            color: white;
        }
        
        .status-sold {
            background: var(--danger);
            color: white;
        }
        
        .status-hidden {
            background: var(--text-light);
            color: white;
        }
        
        .thumbnails-container {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            flex-shrink: 0;
            transition: all var(--transition-fast);
        }
        
        .thumbnail.active {
            border-color: var(--accent-600);
        }
        
        .thumbnail:hover {
            border-color: var(--accent-400);
            transform: translateY(-2px);
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Product Info */
        .product-info-sidebar {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .product-header {
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .product-category {
            display: inline-block;
            padding: 0.4rem 1rem;
            background: var(--primary-100);
            color: var(--primary-700);
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .product-title {
            font-size: 2rem;
            color: var(--primary-700);
            margin: 0 0 1rem 0;
            line-height: 1.3;
        }
        
        .product-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-600);
            margin: 1rem 0;
        }
        
        .product-price.none {
            color: var(--text-light);
            font-size: 1.8rem;
        }
        
        .product-meta {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-color);
        }
        
        .meta-item i {
            color: var(--accent-600);
            width: 20px;
        }
        
        .seller-card {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            margin: 1.5rem 0;
        }
        
        .seller-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .seller-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .seller-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .seller-info h4 {
            margin: 0 0 0.25rem 0;
            color: var(--primary-700);
        }
        
        .seller-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .seller-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            padding: 1rem 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            margin: 1rem 0;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
        }
        
        .stat-value {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--accent-600);
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        
        .product-description {
            padding: 2rem;
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--border-color);
            margin: 1.5rem 0;
        }
        
        .product-description h3 {
            color: var(--primary-700);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .product-description-content {
            line-height: 1.8;
            color: var(--text-color);
            white-space: pre-line;
        }
        
        /* Action Buttons */
        .product-actions-detail {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        
        .btn-action {
            flex: 1;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all var(--transition-normal);
        }
        
        .btn-message-seller {
            background: linear-gradient(45deg, var(--accent-600), var(--accent-400));
            color: white;
        }
        
        .btn-message-seller:hover:not(.disabled) {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-back {
            background: var(--surface);
            color: var(--primary-700);
            border: 2px solid var(--border-color);
        }
        
        .btn-back:hover {
            background: var(--primary-100);
            border-color: var(--primary-300);
            transform: translateY(-2px);
        }
        
        .btn-action.disabled,
        .btn-action:disabled {
            background: var(--border-color);
            color: var(--text-light);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        /* Similar Products */
        .similar-products {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        
        .section-title {
            color: var(--primary-700);
            font-size: 1.75rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .similar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .product-actions-detail {
                flex-direction: column;
            }
            
            .main-image-container {
                height: 300px;
            }
            
            .product-title {
                font-size: 1.5rem;
            }
            
            .product-price {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include './views/navbar.php'; ?>
    
    <section class="section-padding">
        <div class="product-detail-container">
            
            <!-- Breadcrumb -->
            <div style="margin-bottom: 2rem; color: var(--text-light);">
                <a href="shop.php" style="color: var(--accent-600); text-decoration: none;">Termékek</a>
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
                             src="<?php echo htmlspecialchars($images[0]['image_path']); ?>" 
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
                
                <!-- Right: Product Info -->
                <div class="product-info-sidebar">
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
                    
                    <!-- Product Meta -->
                    <div class="product-meta">
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
                    </div>
                    
                    <!-- Seller Card -->
                    <div class="seller-card">
                        <div class="seller-header">
                            <div class="seller-avatar">
                                <img src="<?php echo htmlspecialchars($product['seller_avatar']); ?>" 
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
                            <div class="stat-item">
                                <div class="stat-value">98%</div>
                                <div class="stat-label">Válaszidő</div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="profile.php?id=<?php echo $product['seller_id']; ?>" 
                               class="btn-back" style="display: inline-flex; padding: 0.5rem 1.5rem;">
                                <i class="fas fa-user-circle"></i>
                                Profil megtekintése
                            </a>
                        </div>
                    </div>
                    
                    <!-- Product Description -->
                    <div class="product-description">
                        <h3>Termék leírása</h3>
                        <div class="product-description-content">
                            <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="product-actions-detail">
                        <a href="shop.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Vissza a termékekhez
                        </a>
                        
                        <?php if ($product['product_status'] === 'active'): ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($product['seller_id'] != $_SESSION['user_id']): ?>
                                    <a href="conversation.php?product_id=<?php echo $product_id; ?>" 
                                       class="btn-message-seller">
                                        <i class="fas fa-comment-dots"></i>
                                        Üzenet küldése az eladónak
                                    </a>
                                <?php else: ?>
                                    <a href="edit_product.php?id=<?php echo $product_id; ?>" 
                                       class="btn-message-seller" style="background: var(--primary-500);">
                                        <i class="fas fa-edit"></i>
                                        Termék szerkesztése
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php?redirect=product_detail.php?id=<?php echo $product_id; ?>" 
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
                        <?php include 'product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </section>
    
    <script>
        // Képváltó funkció
        function changeMainImage(imageSrc, thumbnailElement) {
            // Fő kép frissítése
            document.getElementById('main-product-image').src = imageSrc;
            
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