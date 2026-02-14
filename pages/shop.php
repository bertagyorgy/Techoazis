<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../core/config.php';

require_once ROOT_PATH . '/app/db.php';

// Szűrők beállítása
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';

// SQL query - CSAK AKTÍV termékek, nincs készlet ellenőrzés
$sql = "SELECT p.*, u.username as seller_username, u.username_slug AS user_slug,
               (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) as main_image
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
    <title>Techoázis | Vásárlás</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/filter_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/shop_style.css">
    

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
</head>

<body>
    <?php include ROOT_PATH . '/views/navbar.php'; ?>
    
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
                    <h1 class="section-title">Tech termékek vásárlása</h1>
                    <p class="text-center" style="color: var(--text-light); margin-bottom: 2rem;">
                        Fedezd fel a közösség által felkínált technológiai termékeket
                    </p>
                    
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <i class="fas fa-search"></i>
                            <h1 class="section-title">Nincs találat</h1>
                            <p>Próbálj más szűrőket, vagy adj fel egy új hirdetést!</p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="<?= BASE_URL ?>/pages/add_product.php" class="filter-btn apply">
                                    Új termék feladása
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
                                    'main_image' => $product['main_image'] ?? 'uploads/products/default_product.png',
                                    'category' => $product['category'],
                                    'product_status' => $product['product_status'],
                                    'created_at' => $product['created_at']
                                ];
                                ?>
                                <?php include ROOT_PATH . '/views/product_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="custom-container">
            <div class="grid-row">
                <div class="grid-col-4">
                    <div class="footer-brand">
                        <h3 class="footer-subtitle">Techoázis</h3>
                        <p class="footer-description">
                            A hely, ahol a technológia, a közösség és az innováció találkozik.
                        </p>
                    </div>
                </div>
                <div class="grid-col-4 footer-nav">
                    <h3 class="footer-title">Navigáció</h3>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/index.php" class="footer-link"><i class="fas fa-home"></i> Főoldal</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/shop.php" class="footer-link"><i class="fas fa-shopping-cart"></i> Webshop</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/forum.php" class="footer-link"><i class="fas fa-comments"></i> Csevegés</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/articles.php" class="footer-link"><i class="fa-solid fa-pen"></i>Cikkek</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/about_us.php" class="footer-link"><i class="fa-solid fa-address-card"></i>Rólunk</a></li>
                        <?php
                        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                            <li><a href="<?= BASE_URL ?>/admin/admin.php" class="footer-link"><i class="fas fa-cog"></i> Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="grid-col-4 footer-social">
                    <h3 class="footer-title">Kövess minket</h3>
                    <div class="social-icons-wrapper">
                        <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon" aria-label="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
                        <a href="#" class="social-icon" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Techoázis. Minden jog fenntartva.
            </div>
        </div>
    </footer>
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
            window.location.href = '<?= BASE_URL ?>/pages/shop.php';
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