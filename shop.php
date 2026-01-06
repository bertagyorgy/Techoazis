<?php
session_start();
require_once __DIR__ . '/app/db.php';

// Szűrők beállítása
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';

// SQL query - CSAK AKTÍV termékek, nincs készlet ellenőrzés
$sql = "SELECT p.*, u.username as seller_username, 
               (SELECT image_path FROM images WHERE product_id = p.product_id LIMIT 1) as main_image
        FROM products p
        JOIN users u ON p.seller_user_id = u.user_id
        WHERE p.product_status = 'active'";

$params = [];

// Kategória szűrés
if ($category_filter && $category_filter !== 'all') {
    $sql .= " AND p.category = ?";
    $params[] = $category_filter;
}

// Keresés szűrés
if ($search_query) {
    $sql .= " AND (p.product_name LIKE ? OR p.product_description LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Ár szűrés
if (is_numeric($price_min)) {
    $sql .= " AND p.price >= ?";
    $params[] = $price_min;
}

if (is_numeric($price_max)) {
    $sql .= " AND p.price <= ?";
    $params[] = $price_max;
}

$sql .= " ORDER BY p.created_at DESC";

// Termékek lekérése
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

// Kategóriák lekérése a szűrőhöz
$categories_result = $conn->query("SELECT DISTINCT category FROM products WHERE product_status = 'active' ORDER BY category");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoazis | Shop</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="./static/filter_system.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/utility_classes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/index.js" defer></script>
</head>
<style>
    body {
        background-color: var(--background);
    }
    
    .custom-container {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
        box-sizing: border-box;
    }

    @media (min-width: 576px) {
        .section-title { font-size: 2.5rem; }
        .custom-container { max-width: 540px; }
    }
    @media (min-width: 768px) { .custom-container { max-width: 720px; } }
    @media (min-width: 992px) { .custom-container { max-width: 960px; } }
    @media (min-width: 1200px) { .custom-container { max-width: 1140px; } }

    .grid-row {
        display: flex;
        flex-wrap: wrap;
        margin: -0.75rem;
    }
    
    /* ===============================
       FILTER + PRODUCT LAYOUT 
    ================================*/
    .shop-layout {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }

    .shop-sidebar {
        width: 280px;
        flex-shrink: 0;
        background: var(--surface);
        border-radius: var(--border-radius-lg);
        padding: 1.5rem;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        position: sticky;
        top: 100px;
        max-height: calc(100vh - 120px);
        /*overflow-y: auto;*/
    }

    .shop-content {
        flex: 1;
    }

    /* Reszponzív */
    @media (max-width: 992px) {
        .shop-layout {
            flex-direction: column;
        }
        .shop-sidebar {
            width: 100%;
            position: static;
            max-height: none;
        }
    }
    
    /* Filter styles */
    .filter-section h3 {
        color: var(--primary-700);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .filter-group {
        margin-bottom: 1.5rem;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-color);
    }
    
    .filter-select, .filter-input {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius-md);
        background: var(--background);
        color: var(--text-color);
        font-size: 1rem;
    }
    
    .filter-select:focus, .filter-input:focus {
        outline: none;
        border-color: var(--accent-600);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }
    
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .filter-tag {
        padding: 0.5rem 1rem;
        background: var(--primary-100);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        font-size: 0.9rem;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    
    .filter-tag:hover {
        background: var(--accent-200);
        border-color: var(--accent-600);
    }
    
    .filter-tag.active {
        background: var(--accent-600);
        color: white;
        border-color: var(--accent-600);
    }
    
    .filter-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .filter-btn {
        flex: 1;
        padding: 0.75rem;
        border: none;
        border-radius: var(--border-radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    
    .filter-btn.apply {
        background: linear-gradient(45deg, var(--accent-600), var(--accent-400));
        color: white;
    }
    
    .filter-btn.reset {
        background: var(--surface);
        color: var(--text-color);
        border: 2px solid var(--border-color);
    }
    
    .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    /* Product grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .no-products {
        text-align: center;
        padding: 3rem;
        background: var(--surface);
        border-radius: var(--border-radius-lg);
        border: 1px solid var(--border-color);
    }
    
    .no-products i {
        font-size: 3rem;
        color: var(--text-light);
        margin-bottom: 1rem;
    }
    
    .no-products h3 {
        color: var(--primary-700);
        margin-bottom: 0.5rem;
    }
    
    .no-products p {
        color: var(--text-light);
    }
</style>
<body>
    <?php include './views/navbar.php'; ?>
    
    <section class="section-padding">
        <div class="custom-container">
            <div class="shop-layout">
                
                <!-- =============================== -->
                <!--          SZŰRŐK PANEL          -->
                <!-- =============================== -->
                <aside class="shop-sidebar">
                    <h3>Szűrők</h3>
                    
                    <form id="filter-form" method="GET">
                        <!-- Keresés -->
                        <div class="filter-group">
                            <label for="search">Keresés</label>
                            <input type="text" id="search" name="search" class="filter-input" 
                                    placeholder="Termék neve..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        
                        <!-- Kategória -->
                        <div class="filter-group">
                            <label for="category">Kategória</label>
                            <select id="category" name="category" class="filter-select">
                                <option value="">Összes kategória</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                        <?php echo $category_filter == $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Ár tartomány -->
                        <div class="filter-group">
                            <label>Ár tartomány (Ft)</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <input type="number" name="price_min" class="filter-input" 
                                        placeholder="Min" value="<?php echo htmlspecialchars($price_min); ?>">
                                <input type="number" name="price_max" class="filter-input" 
                                        placeholder="Max" value="<?php echo htmlspecialchars($price_max); ?>">
                            </div>
                        </div>
                        
                        <!-- Gyors ár szűrők -->
                        <div class="filter-group">
                            <label>Gyors szűrés</label>
                            <div class="filter-tags">
                                <div class="filter-tag" data-price-min="0" data-price-max="10000">0-10 000 Ft</div>
                                <div class="filter-tag" data-price-min="10000" data-price-max="50000">10-50 000 Ft</div>
                                <div class="filter-tag" data-price-min="50000" data-price-max="100000">50-100 000 Ft</div>
                                <div class="filter-tag" data-price-min="100000" data-price-max="">100 000 Ft+</div>
                            </div>
                        </div>
                        
                        <!-- Szűrés gombok -->
                        <div class="filter-actions">
                            <button type="submit" class="filter-btn apply">
                                <i class="fas fa-filter"></i> Szűrés
                            </button>
                            <button type="button" id="reset-filters" class="filter-btn reset">
                                <i class="fas fa-redo"></i> Törlés
                            </button>
                        </div>
                    </form>
                </aside>
                
                <!-- =============================== -->
                <!--          TERMÉK LISTA          -->
                <!-- =============================== -->
                <div class="shop-content">
                    <h1 class="section-title">Tech cuccok vásárlása</h1>
                    <p class="text-center" style="color: var(--text-light); margin-bottom: 2rem;">
                        Fedezd fel a közösség által felkínált technológiai termékeket
                    </p>
                    
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <i class="fas fa-search"></i>
                            <h3>Nincs találat</h3>
                            <p>Próbálj más szűrőket, vagy adj fel egy új hirdetést!</p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="add_product.php" class="filter-btn apply" style="margin-top: 1rem; display: inline-block;">
                                    <i class="fas fa-plus"></i> Új termék feladása
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <?php 
                                // Termék adatok átadása a kártyának
                                $product_card_data = [
                                    'product_id' => $product['product_id'],
                                    'product_name' => $product['product_name'],
                                    'price' => $product['price'],
                                    'seller_username' => $product['seller_username'],
                                    'main_image' => $product['main_image'] ?? 'images/default_product.jpg',
                                    'category' => $product['category'],
                                    'product_status' => $product['product_status'],
                                    'created_at' => $product['created_at']
                                ];
                                ?>
                                <?php include 'product_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        // Gyors ár szűrők
        document.querySelectorAll('.filter-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                const min = this.dataset.priceMin;
                const max = this.dataset.priceMax;
                
                document.querySelector('input[name="price_min"]').value = min || '';
                document.querySelector('input[name="price_max"]').value = max || '';
                document.getElementById('filter-form').submit();
            });
        });
        
        // Szűrők törlése
        document.getElementById('reset-filters').addEventListener('click', function() {
            window.location.href = 'shop.php';
        });
        
        // Aktív szűrők jelölése
        document.querySelectorAll('.filter-tag').forEach(tag => {
            const min = tag.dataset.priceMin;
            const max = tag.dataset.priceMax;
            
            if ((!min || min === '<?php echo $price_min; ?>') && 
                (!max || max === '<?php echo $price_max; ?>')) {
                tag.classList.add('active');
            }
        });
    </script>
</body>
</html>