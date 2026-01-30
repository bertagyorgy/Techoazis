<?php
// product_card.php — Egyetlen termékkártya megjelenítése Marketplace stílusban

if (!isset($product) || !is_array($product)) {
    echo "<p>Hiba: Nincs termékadat a kártyához.</p>";
    return;
}

$product_id = $product['product_id'] ?? 0;
$product_name = htmlspecialchars($product['product_name'] ?? 'Névtelen termék');
$price_value = $product['price'] ?? 0;
$price = $price_value > 0 ? number_format($price_value, 0, '', ' ') . ' Ft' : 'Alkuképes';
$seller_username = htmlspecialchars($product['seller_username'] ?? 'Ismeretlen eladó');
$category = htmlspecialchars($product['category'] ?? 'Egyéb');
$product_status = $product['product_status'] ?? 'active';
$created_date = date('Y.m.d.', strtotime($product['created_at'] ?? 'now'));

// Kép kezelés
$main_image = $product['main_image'] ?? 'images/default_product.jpg';
$image_url = htmlspecialchars($main_image);

// Státusz szöveg és stílus
$status_text = '';
$status_class = '';

switch ($product_status) {
    case 'active':
        $status_text = 'Aktív';
        $status_class = 'status-active';
        break;
    case 'sold':
        $status_text = 'Eladva';
        $status_class = 'status-sold';
        break;
    case 'hidden':
        $status_text = 'Rejtett';
        $status_class = 'status-hidden';
        break;
    default:
        $status_text = 'Ismeretlen';
        $status_class = 'status-hidden';
}
?>

<div class="product-card">
    <div class="product-card-inner">
        <!-- Termék kép -->
        <a href="product_detail.php?id=<?= $product_id ?>" class="product-image-link">
            <div class="product-image-container">
                <img src="<?= $image_url ?>" 
                     alt="<?= $product_name ?>"
                     class="product-image"
                     onerror="this.onerror=null;this.src='images/default_product.jpg';">
                
                <!-- Státusz badge -->
                <div class="product-status-badge <?= $status_class ?>">
                    <?= $status_text ?>
                </div>
                
                <!-- Gyors megtekintés overlay -->
                <div class="quick-view-overlay">
                    <i class="fas fa-eye"></i>
                    <span>Részletek</span>
                </div>
            </div>
        </a>
        
        <!-- Termék információk -->
        <div class="product-info">
            <!-- Kategória címke -->
            <div class="product-category">
                <i class="fas fa-tag"></i>
                <?= $category ?>
            </div>
            
            <!-- Termék név -->
            <h3 class="product-title">
                <a href="product_detail.php?id=<?= $product_id ?>">
                    <?= $product_name ?>
                </a>
            </h3>
            
            <!-- Ár -->
            <div class="product-price">
                <?= $price ?>
            </div>
            
            <!-- Eladó -->
            <div class="product-seller">
                <i class="fas fa-user"></i>
                <span><?= $seller_username ?></span>
            </div>
            
            <!-- Dátum -->
            <div class="product-date">
                <i class="fas fa-calendar"></i>
                <?= $created_date ?>
            </div>
        </div>
        
        <!-- Akció gombok -->
        <div class="product-actions">
            <?php if ($product_status === 'active'): ?>
                <!-- Aktív termék: részletek gomb -->
                <a href="product_detail.php?id=<?= $product_id ?>" class="btn-details">
                    <i class="fas fa-info-circle"></i>
                    Részletek
                </a>
                
                <!-- Gyors üzenet az eladónak (ha be van jelentkezve és nem saját termék) -->
                <?php if (isset($_SESSION['user_id']) && $product['seller_user_id'] != $_SESSION['user_id']): ?>
                    <a href="conversation.php?product_id=<?= $product_id ?>" class="btn-message">
                        <i class="fas fa-comment-dots"></i>
                        Üzenet
                    </a>
                <?php endif; ?>
            <?php elseif ($product_status === 'sold'): ?>
                <!-- Eladott termék: csak részletek -->
                <a href="product_detail.php?id=<?= $product_id ?>" class="btn-details disabled">
                    <i class="fas fa-eye"></i>
                    Megtekintés
                </a>
            <?php else: ?>
                <!-- Rejtett termék: nem elérhető -->
                <button class="btn-details disabled" disabled>
                    <i class="fas fa-lock"></i>
                    Nem elérhető
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="./static/product_card_style.css">
