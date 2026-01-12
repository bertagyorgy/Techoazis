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

<style>
    /* Product Card Styles */
    .product-card {
        background: var(--surface);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        border: 1px solid var(--neutral-500);
        transition: all var(--transition-normal);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-color: var(--accent-400);
    }
    
    .product-card-inner {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    /* Image Container */
    .product-image-container {
        position: relative;
        overflow: hidden;
        height: 200px;
        background: var(--border-color);
        object-fit: scale-down;
    }
    
    .product-image {
        width: 100%;
        height: 100%;
        object-fit: scale-down;
        transition: transform var(--transition-normal);
    }
    
    .product-card:hover .product-image {
        transform: scale(1.05);
    }
    
    .product-image-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    
    /* Status Badge */
    .product-status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
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
    
    /* Quick View Overlay */
    .quick-view-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(10, 25, 47, 0.8);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        opacity: 0;
        transition: opacity var(--transition-normal);
        z-index: 1;
    }
    
    .product-card:hover .quick-view-overlay {
        opacity: 1;
    }
    
    .quick-view-overlay i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .quick-view-overlay span {
        font-weight: 600;
        font-size: 1rem;
    }
    
    /* Product Info */
    .product-info {
        padding: 1.5rem;
        flex: 1;
    }
    
    .product-category {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        background: var(--primary-100);
        color: var(--primary-700);
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }
    
    .product-category i {
        margin-right: 0.3rem;
    }
    
    .product-title {
        margin: 0 0 1rem 0;
        font-size: 1.2rem;
        line-height: 1.4;
    }
    
    .product-title a {
        color: var(--text-light);
        text-decoration: none;
        transition: color var(--transition-fast);
    }
    
    .product-title a:hover {
        color: var(--accent-600);
    }
    
    .product-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--accent-600);
        margin-bottom: 1rem;
    }
    
    .product-seller,
    .product-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .product-seller i,
    .product-date i {
        width: 16px;
        text-align: center;
    }
    
    /* Action Buttons */
    .product-actions {
        padding: 0 1.5rem 1.5rem 1.5rem;
        display: flex;
        gap: 0.75rem;
    }
    
    .btn-details,
    .btn-message {
        flex: 1;
        padding: 0.75rem;
        text-align: center;
        border-radius: var(--border-radius-md);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-details {
        background: linear-gradient(45deg, var(--primary-500), var(--primary-300));
        color: white;
    }
    
    .btn-details:hover:not(.disabled) {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .btn-message {
        background: linear-gradient(45deg, var(--accent-600), var(--accent-400));
        color: white;
    }
    
    .btn-message:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .btn-details.disabled,
    .btn-details:disabled {
        background: var(--border-color);
        color: var(--text-light);
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .product-actions {
            flex-direction: column;
        }
        
        .product-image-container {
            height: 180px;
        }
    }
</style>