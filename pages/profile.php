<?php 
// Bekötjük a háttérlogikát. Cseréld ki az elérési utat, ha máshol van a fájl!
require_once __DIR__ . '/../core/config.php'; 

// Innentől már csak ROOT_PATH-ot használunk
require_once ROOT_PATH . '/app/profile_logic.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Felhasználói profil a Techoázison: értékelések, hirdetések és aktivitás egy helyen. Ellenőrizd a megbízhatóságot vásárlás előtt.">
    <title>Techoázis | <?php echo htmlspecialchars($user['username']); ?> profilja</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile_style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

<div class="profile-dashboard">
    <aside class="profile-sidebar">
        <img src="<?= $profile_image ?>"  alt="Profilkép" class="profile-avatar" onerror="this.src='<?= BASE_URL ?>/uploads/profile_images/anonymous.png'">
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
            <a href="<?= BASE_URL ?>/pages/profile_edit.php" class="profile-btn profile-btn-secondary">
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
                <a href="<?= BASE_URL ?>/pages/conversation.php?conv_id=<?php echo $conv['conversation_id']; ?>&product_id=<?php echo $conv['product_id']; ?>" class="conversation-link">
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
                <a href="<?= BASE_URL ?>/pages/product_detail.php?id=<?php echo $product['product_id']; ?>" class="product-card-link">
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>"
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
            <a href="<?= BASE_URL ?>/pages/add_product.php" class="profile-btn profile-btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Új termék feladása
            </a>
            <?php endif; ?>
        </section>

        <?php if (count($reviews) > 0): ?>
        <section class="reviews-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="section-title">Legutóbbi értékelések</h3>
            </div>
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
<?php 
// Kapcsolat lezárása a legvégén
$conn->close(); 
?>