<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Config betöltése (ez definiálja a ROOT_PATH-ot és a BASE_URL-t)
require_once __DIR__ . '/config.php';

// 2. Adatbázis betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/db.php';

// 3. Biztonsági ellenőrzés javítása
if (!isset($_SESSION['username'])) {
    // PHP alapú átirányítás a BASE_URL használatával
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$viewer_id = (int)($_SESSION['user_id'] ?? 0);
if ($viewer_id <= 0) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

// slug param (pl. profile?u=admin)
$slug = isset($_GET['u']) ? trim($_GET['u']) : null;

// 1) Nézett user lekérése slug alapján (ha van)
if ($slug !== null && $slug !== '') {
    $stmt = $conn->prepare("
        SELECT user_id, username, username_slug, email, registration_date, user_role, profile_image,
               total_posts, total_comments, sold_items, bought_items, avg_rating 
        FROM users 
        WHERE username_slug = ? 
        LIMIT 1
    ");
    $stmt->bind_param("s", $slug);
} else {
    // 2) Ha nincs slug -> saját profil
    $stmt = $conn->prepare("
        SELECT user_id, username, username_slug, email, registration_date, user_role, profile_image,
               total_posts, total_comments, sold_items, bought_items, avg_rating 
        FROM users 
        WHERE user_id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $viewer_id);
}

$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    exit("Nincs ilyen profil.");
}

$is_owner = ((int)$user['user_id'] === $viewer_id);
$profile_user_id = (int)$user['user_id'];

// Aktív termékek száma (nézett profilhoz!)
$stmt = $conn->prepare("SELECT COUNT(*) as active_count FROM products WHERE seller_user_id = ? AND product_status = 'active'");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$active_products = (int)($stmt->get_result()->fetch_assoc()['active_count'] ?? 0);
$stmt->close();

// Eladott termékek száma (nézett profilhoz!)
$stmt = $conn->prepare("SELECT COUNT(*) as sold_count FROM products WHERE seller_user_id = ? AND product_status = 'sold'");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$sold_products = (int)($stmt->get_result()->fetch_assoc()['sold_count'] ?? 0);
$stmt->close();

// Feltett termékek (max 6) (nézett profilhoz!)
$stmt = $conn->prepare("
    SELECT product_id, product_name, category, price, product_status, 
           created_at, (SELECT image_path FROM images WHERE product_id = products.product_id AND is_primary = 1 LIMIT 1) as image_path
    FROM products 
    WHERE seller_user_id = ?
    ORDER BY created_at DESC 
    LIMIT 6
");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$products_result = $stmt->get_result();
$user_products = [];
while ($row = $products_result->fetch_assoc()) {
    $row['image_path'] = $row['image_path'] ?? BASE_URL . '/uploads/products/default_product.png';
    $user_products[] = $row;
}
$stmt->close();

// Értékelések (nézett profilhoz!)
$stmt = $conn->prepare("
    SELECT r.*, u.username as reviewer_name
    FROM reviews r
    JOIN users u ON r.buyer_user_id = u.user_id
    WHERE r.seller_user_id = ?
    ORDER BY r.review_date DESC
    LIMIT 3
");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Beszélgetések csak a saját profilnál!
$conversations = [];
if ($is_owner) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                c.conversation_id,
                c.product_id,
                p.product_name,
                u.username AS other_user,

                (SELECT COUNT(*) 
                FROM messages m
                WHERE m.conversation_id = c.conversation_id
                AND m.sender_user_id != ?
                AND m.is_read = 0
                ) AS unread_count,

                COALESCE(
                    (SELECT MAX(m2.sent_at) FROM messages m2 WHERE m2.conversation_id = c.conversation_id),
                    c.updated_at,
                    c.created_at
                ) AS last_activity

            FROM conversations c
            JOIN products p ON p.product_id = c.product_id
            JOIN users u ON (
                (c.seller_user_id = u.user_id AND c.seller_user_id != ?) OR
                (c.buyer_user_id  = u.user_id AND c.buyer_user_id  != ?)
            )
            WHERE (c.seller_user_id = ? OR c.buyer_user_id = ?)
            ORDER BY last_activity DESC
            LIMIT 5
        ");
        $stmt->bind_param("iiiii", $viewer_id, $viewer_id, $viewer_id, $viewer_id, $viewer_id);
        $stmt->execute();
        $conversations_result = $stmt->get_result();
        while ($row = $conversations_result->fetch_assoc()) {
            $conversations[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Conversation error: " . $e->getMessage());
    }
}

$profile_image = !empty($user['profile_image']) 
    ? BASE_URL . '/' . htmlspecialchars($user['profile_image']) 
    : BASE_URL . '/images/profile_images/anonymous.png';$user_role_display = ($user['user_role'] ?? '') === 'A' ? 'Adminisztrátor' : 'Felhasználó';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | <?php echo htmlspecialchars($user['username']); ?> profilja</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/profile_style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/static/index.js" defer></script>
</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

<div class="profile-dashboard">
    <aside class="profile-sidebar">
        <img src="<?php echo $profile_image; ?>" alt="Profilkép" class="profile-avatar">
        <h2 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h2>
        <span class="profile-role"><?php echo $user_role_display; ?></span>

        <div class="rating-display">
            <strong>Értékelés:</strong>
            <span class="rating-stars">
                <?php
                $rating = $user['avg_rating'] ?? 0;
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= floor($rating)) {
                        echo '<i class="fas fa-star"></i>';
                    } elseif ($i == ceil($rating) && fmod($rating, 1) > 0) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                ?>
                <span style="margin-left: 0.5rem; font-weight: 600;">(<?php echo number_format((float)$rating, 1); ?>)</span>
            </span>
        </div>
        <div class="rating-display">
            <strong>Regisztrált:<br></strong>
            <?php 
                $date = new DateTime($user['registration_date']);
                echo htmlspecialchars($date->format('Y-m-d'));
            ?>
        </div>
        <?php if ($is_owner): ?>
        <div class="profile-actions">
            <a href="<?= BASE_URL ?>/profile_edit.php" class="profile-btn profile-btn-secondary">
                <i class="fas fa-user-edit"></i> Profil szerkesztése
            </a>
            <button class="profile-btn profile-btn-secondary theme-toggle">
                <i class="fa-solid fa-moon"></i> Téma váltás
            </button>
            <button class="profile-btn profile-btn-danger" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt"></i> Kijelentkezés
            </button>
        </div>
        <?php endif; ?>
    </aside>

    <main class="profile-main">
        <div class="main-content-header">
            <h1>Statisztika</h1>
            <div class="product-status-badges">
                <span class="product-count-badge" style="background: var(--success); color: white;">
                    <i class="fas fa-check-circle"></i> <?php echo $active_products; ?> aktív
                </span>
                <span class="product-count-badge" style="background: var(--neutral-500); color: white;">
                    <i class="fas fa-check"></i> <?php echo $sold_products; ?> eladott
                </span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-file-alt stat-icon"></i>
                <div class="stat-value"><?php echo (int)$user['total_posts']; ?></div>
                <div class="stat-label">Posztok</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-comment stat-icon"></i>
                <div class="stat-value"><?php echo (int)$user['total_comments']; ?></div>
                <div class="stat-label">Kommentek</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart stat-icon"></i>
                <div class="stat-value"><?php echo (int)$user['bought_items']; ?></div>
                <div class="stat-label">Vásárolt</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-tag stat-icon"></i>
                <div class="stat-value"><?php echo (int)$user['sold_items']; ?></div>
                <div class="stat-label">Eladott</div>
            </div>
        </div>

        <?php if ($is_owner): ?>
        <section class="conversations-list">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="section-title">Legutóbbi beszélgetések</h3>
            </div>

            <?php if (count($conversations) > 0): ?>
                <?php foreach ($conversations as $conv): ?>
                <a href="<?= BASE_URL ?>/conversation.php?conv_id=<?php echo $conv['conversation_id']; ?>&product_id=<?php echo $conv['product_id']; ?>" class="conversation-link">
                    <div class="conversation-item <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?>">
                        <div class="conversation-product">
                            <?php echo htmlspecialchars($conv['product_name']); ?>
                            <?php if ($conv['unread_count'] > 0): ?>
                            <span class="unread-badge">
                                <?php echo (int)$conv['unread_count']; ?> új
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="conversation-user">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($conv['other_user']); ?>
                        </div>
                        <div class="conversation-time">
                            <?php echo date('Y.m.d H:i', strtotime($conv['last_activity'])); ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <p>Még nincsenek beszélgetéseid</p>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <section class="products-section">
            <div class="products-header">
                <h3 class="section-title"><?php echo $is_owner ? 'Termékeim' : 'Termékei'; ?></h3>

            </div>

            <?php if (count($user_products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($user_products as $product): ?>
                <a href="<?= BASE_URL ?>/product_detail.php?id=<?php echo $product['product_id']; ?>" class="product-card-link">
                    <div class="product-card">
                        <img src="<?= htmlspecialchars(BASE_URL . "/". $product['image_path']) ?>"
                            alt="<?= htmlspecialchars($product['product_name']) ?>"
                            class="product-image"
                            onerror="this.src='<?= BASE_URL ?>/uploads/products/default_product.png'">

                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                            <?php if ($product['price']): ?>
                            <div class="product-price"><?php echo number_format((float)$product['price'], 0, ',', ' '); ?> Ft</div>
                            <?php endif; ?>
                            <span class="product-status status-<?php echo $product['product_status']; ?>">
                                <?php
                                $status_text = [
                                    'active' => 'Aktív',
                                    'sold' => 'Eladva',
                                    'hidden' => 'Rejtett'
                                ];
                                echo $status_text[$product['product_status']] ?? $product['product_status'];
                                ?>
                            </span>
                            <div style="font-size: 0.75rem; color: var(--neutral-500); margin-top: 0.5rem;">
                                <?php echo date('Y.m.d', strtotime($product['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p><?php echo $is_owner ? 'Még nem tettél fel terméket' : 'Még nem tett fel terméket'; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($is_owner): ?>
            <a href="<?= BASE_URL ?>/add_product.php" class="profile-btn profile-btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Új termék feladása
            </a>
            <?php endif; ?>
        </section>

        <?php if (count($reviews) > 0): ?>
        <section class="reviews-section">
            <h3 class="section-title">Legutóbbi értékelések</h3>
            <?php foreach ($reviews as $review): ?>
            <div class="review-item">
                <div class="review-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?php echo $i > (int)$review['rating'] ? '-o' : ''; ?>"></i>
                    <?php endfor; ?>
                    <span style="margin-left: 0.5rem; font-weight: 600;"><?php echo (int)$review['rating']; ?>/5</span>
                </div>
                <?php if (!empty($review['comment'])): ?>
                <p class="review-comment">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                <?php endif; ?>
                <div class="review-author">
                    <span>- <?php echo htmlspecialchars($review['reviewer_name']); ?></span>
                    <span><?php echo date('Y.m.d', strtotime($review['review_date'])); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

    </main>
</div>

<script>
function confirmLogout() {
    if (confirm('Biztosan ki szeretnél jelentkezni?')) {
        window.location.href = '<?= BASE_URL ?>/views/logout.php';
    }
}

function confirmDeleteAccount() {
    if (confirm('⚠️ Figyelem! A fiók törlésével minden adatod véglegesen törlődik.\n\nBiztosan folytatod?')) {
        window.location.href = '<?= BASE_URL ?>/app/delete_account.php';
    }
}


</script>
</body>
</html>
<?php $conn->close(); ?>
