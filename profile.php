<?php
session_start();
include './app/db.php';

if (!isset($_SESSION['username'])) {
    echo "<script>window.location.href='../views/login.php';</script>";
    exit();
}

$username = $_SESSION['username'];
$current_user_id = $_SESSION['user_id'];

// Felhasználó alapadatai
$query = "SELECT user_id, username, email, registration_date, user_role, profile_image,
                 total_posts, total_comments, sold_items, bought_items, avg_rating 
          FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Aktív termékek száma
$stmt = $conn->prepare("SELECT COUNT(*) as active_count FROM products WHERE seller_user_id = ? AND product_status = 'active'");
$stmt->bind_param('i', $current_user_id);
$stmt->execute();
$active_result = $stmt->get_result();
$active_products = $active_result->fetch_assoc()['active_count'] ?? 0;
$stmt->close();

// Eladott termékek száma
$stmt = $conn->prepare("SELECT COUNT(*) as sold_count FROM products WHERE seller_user_id = ? AND product_status = 'sold'");
$stmt->bind_param('i', $current_user_id);
$stmt->execute();
$sold_result = $stmt->get_result();
$sold_products = $sold_result->fetch_assoc()['sold_count'] ?? 0;
$stmt->close();

// Feltett termékek (max 6)
$stmt = $conn->prepare("
    SELECT product_id, product_name, category, price, product_status, 
           created_at, (SELECT image_path FROM images WHERE product_id = products.product_id AND is_primary = 1 LIMIT 1) as image_path
    FROM products 
    WHERE seller_user_id = ?
    ORDER BY created_at DESC 
    LIMIT 6
");
$stmt->bind_param('i', $current_user_id);
$stmt->execute();
$products_result = $stmt->get_result();
$user_products = [];
while ($row = $products_result->fetch_assoc()) {
    $row['image_path'] = $row['image_path'] ?? './images/no-image.png';
    $user_products[] = $row;
}
$stmt->close();

// Értékelések
$stmt = $conn->prepare("
    SELECT r.*, u.username as reviewer_name
    FROM reviews r
    JOIN users u ON r.buyer_user_id = u.user_id
    WHERE r.seller_user_id = ?
    ORDER BY r.review_date DESC
    LIMIT 3
");
$stmt->bind_param('i', $current_user_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Beszélgetések AJAX-os betöltéshez - most csak alapadatok
$conversations = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            c.conversation_id,
            c.product_id,
            p.product_name,
            u.username as other_user,
            (SELECT COUNT(*) FROM messages m 
             WHERE m.conversation_id = c.conversation_id 
             AND m.sender_user_id != ? 
             AND m.is_read = 0) as unread_count,
            c.updated_at
        FROM conversations c
        JOIN products p ON c.product_id = p.product_id
        JOIN users u ON (
            (c.seller_user_id = u.user_id AND c.seller_user_id != ?) OR 
            (c.buyer_user_id = u.user_id AND c.buyer_user_id != ?)
        )
        WHERE (c.seller_user_id = ? OR c.buyer_user_id = ?)
        ORDER BY c.updated_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("iiiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id);
    $stmt->execute();
    $conversations_result = $stmt->get_result();
    while ($row = $conversations_result->fetch_assoc()) {
        $conversations[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Csak logoljuk a hibát, ne hagyjuk elszállni az oldalt
    error_log("Conversation error: " . $e->getMessage());
}

$profile_image = !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : './images/anonymous.png';
$user_role_display = $user['user_role'] === 'A' ? 'Adminisztrátor' : 'Felhasználó';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | <?php echo htmlspecialchars($user['username']); ?>'s profile</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/comments.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="./static/create_post.css">
    <link rel="stylesheet" href="./static/custom_card.css">
    <link rel="stylesheet" href="./static/feature_cards.css">
    <link rel="stylesheet" href="./static/filter_system.css">
    <link rel="stylesheet" href="./static/forum.css">
    <link rel="stylesheet" href="./static/group_view.css">
    <link rel="stylesheet" href="./static/hero_section.css">
    <link rel="stylesheet" href="./static/loading_animation.css">
    <link rel="stylesheet" href="./static/login_page.css">
    <link rel="stylesheet" href="./static/modern_footer.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/post_card.css">
    <link rel="stylesheet" href="./static/profile_pages.css">
    <link rel="stylesheet" href="./static/responsive_adjustments.css">
    <link rel="stylesheet" href="./static/utility_classes.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/index.js" defer></script>
    <script src="./static/forum.js" defer></script>

    <style>
        /* Profil specifikus stílusok - JAVÍTOTT */
        body {
            padding-top: 70px;

        }
        
        .profile-dashboard {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 150px);
        }

        @media (max-width: 992px) {
            .profile-dashboard {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }

        .profile-sidebar {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            height: fit-content;
            border: 1px solid var(--border-color);
            text-align: center;
            position: sticky;
            top: 100px;
        }

        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .user-info-card {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-300);
            margin-bottom: 1rem;
            transition: transform var(--transition-fast);
        }

        .profile-avatar:hover {
            transform: scale(1.05);
        }

        .profile-username {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .profile-role {
            display: inline-block;
            background: var(--primary-100);
            color: var(--primary-700);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1px solid var(--primary-200);
        }

        .rating-display {
            margin: 1rem 0;
            padding: 0.75rem;
            background: var(--neutral-100);
            border-radius: var(--border-radius-md);
        }

        .rating-display strong {
            color: var(--text-color);
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .profile-btn {
            padding: 0.75rem;
            border-radius: var(--border-radius-md);
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            text-decoration: none;
            text-align: center;
        }

        /* JAVÍTOTT: Ne legyen hover alatt fehér a szöveg */
        .profile-btn-primary {
            background: var(--primary-500);
            color: white !important;
        }

        .profile-btn-primary:hover {
            background: var(--primary-600);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white !important;
        }

        .profile-btn-secondary {
            background: var(--neutral-100);
            color: var(--neutral-700) !important;
            border: 1px solid var(--border-color);
        }

        .profile-btn-secondary:hover {
            background: var(--neutral-200);
            transform: translateY(-2px);
            color: var(--neutral-900) !important;
        }

        .profile-btn-danger {
            background: var(--danger);
            color: white !important;
        }

        .profile-btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white !important;
        }

        /* JAVÍTOTT: Statisztikák jobb oldalon, grid-be rendezve */
        .stats-section {
            margin-top: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            background: var(--surface);
            padding: 1.25rem;
            border-radius: var(--border-radius-md);
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-fast);
            border: 1px solid var(--border-color);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-500);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .conversations-list {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .conversation-item {
            padding: 1rem;
            border-radius: var(--border-radius-md);
            margin-bottom: 0.75rem;
            background: var(--neutral-100);
            transition: all var(--transition-fast);
            cursor: pointer;
            border: 1px solid transparent;
        }

        .conversation-item:hover {
            background: var(--neutral-200);
            border-color: var(--primary-200);
            transform: translateX(5px);
        }

        .conversation-item.unread {
            background: var(--primary-50);
            border-left: 4px solid var(--primary-500);
        }

        .conversation-product {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .conversation-user {
            font-size: 0.875rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .conversation-time {
            font-size: 0.75rem;
            color: var(--neutral-500);
            margin-top: 0.5rem;
            text-align: right;
        }

        .unread-badge {
            background: var(--primary-500);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }

        .products-section {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .product-status-badges {
            display: flex;
            gap: 0.5rem;
        }

        .product-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-fast);
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }

        .product-info {
            padding: 1rem;
        }

        .product-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-size: 1rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            color: var(--primary-500);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .product-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .status-active {
            background: var(--success);
            color: white;
        }

        .status-sold {
            background: var(--neutral-500);
            color: white;
        }

        .status-hidden {
            background: var(--warning);
            color: white;
        }

        .reviews-section {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .review-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--neutral-100);
            border-radius: var(--border-radius-md);
            margin-bottom: 1rem;
        }

        .review-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .review-rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .review-comment {
            color: var(--text-color);
            font-style: italic;
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .review-author {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--neutral-500);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--neutral-300);
        }

        .rating-stars {
            display: inline-flex;
            gap: 0.125rem;
            margin-left: 0.5rem;
        }

        .rating-stars i {
            color: #ffc107;
        }

        .rating-stars i.far {
            color: var(--neutral-300);
        }

        .badge-collection {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--neutral-100);
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            border: 1px solid var(--border-color);
        }

        .badge i {
            color: var(--warning);
        }

        .main-content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .main-content-header h1 {
            color: var(--text-color);
            font-size: 1.75rem;
            margin: 0;
        }

        .btn-view-all {
            background: var(--primary-100);
            color: var(--primary-700);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-md);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all var(--transition-fast);
            border: 1px solid var(--primary-200);
        }

        .btn-view-all:hover {
            background: var(--primary-200);
            color: var(--primary-800);
        }
        
        /* JAVÍTOTT: Beszélgetés linkek átirányítása helyes oldalra */
        .conversation-link {
            color: inherit;
            text-decoration: none;
            display: block;
        }
        
        .conversation-link:hover {
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php include './views/navbar.php'; ?>

<div class="profile-dashboard">
    <!-- Bal oldali sidebar -->
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
                <span style="margin-left: 0.5rem; font-weight: 600;">(<?php echo number_format($rating, 1); ?>)</span>
            </span>
        </div>

        <div class="profile-actions">
            <a href="profile_edit.php?action=image" class="profile-btn profile-btn-primary">
                <i class="fas fa-camera"></i> Profilkép módosítása
            </a>
            <a href="profile_edit.php" class="profile-btn profile-btn-secondary">
                <i class="fas fa-user-edit"></i> Profil szerkesztése
            </a>
            <a href="shop.php?my_products=1" class="profile-btn profile-btn-secondary">
                <i class="fas fa-box"></i> Termékeim
            </a>
            <button class="profile-btn profile-btn-danger" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt"></i> Kijelentkezés
            </button>
        </div>

        <!-- Kitüntetések -->
        <?php
        $badges_stmt = $conn->prepare("
            SELECT b.badge_name, b.icon, b.badge_description
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.badge_id
            WHERE ub.user_id = ?
            ORDER BY ub.earned_at DESC
            LIMIT 3
        ");
        $badges_stmt->bind_param('i', $current_user_id);
        $badges_stmt->execute();
        $badges_result = $badges_stmt->get_result();
        if ($badges_result->num_rows > 0): ?>
        <div class="stats-section">
            <h3 class="section-title">Kitüntetéseim</h3>
            <div class="badge-collection">
                <?php while ($badge = $badges_result->fetch_assoc()): ?>
                <div class="badge" title="<?php echo htmlspecialchars($badge['badge_description']); ?>">
                    <i class="fas fa-<?php echo $badge['icon'] ?? 'award'; ?>"></i>
                    <?php echo htmlspecialchars($badge['badge_name']); ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; 
        $badges_stmt->close(); ?>
    </aside>

    <!-- Fő tartalom -->
    <main class="profile-main">
        <!-- JAVÍTOTT: Statisztikák fejlett elrendezésben -->
        <div class="main-content-header">
            <h1>Profil statisztikák</h1>
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
                <div class="stat-value"><?php echo $user['total_posts']; ?></div>
                <div class="stat-label">Posztok</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-comment stat-icon"></i>
                <div class="stat-value"><?php echo $user['total_comments']; ?></div>
                <div class="stat-label">Kommentek</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart stat-icon"></i>
                <div class="stat-value"><?php echo $user['bought_items']; ?></div>
                <div class="stat-label">Vásárolt</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-tag stat-icon"></i>
                <div class="stat-value"><?php echo $user['sold_items']; ?></div>
                <div class="stat-label">Eladott</div>
            </div>
        </div>

        <!-- Aktív beszélgetések -->
        <section class="conversations-list">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="section-title">Legutóbbi beszélgetések</h3>
                <a href="conversations.php" class="btn-view-all">
                    Összes megtekintése
                </a>
            </div>
            
            <?php if (count($conversations) > 0): ?>
                <?php foreach ($conversations as $conv): ?>
                <!-- JAVÍTOTT: Helyes link a conversation.php-ra -->
                <a href="conversation.php?conv_id=<?php echo $conv['conversation_id']; ?>" class="conversation-link">
                    <div class="conversation-item <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?>">
                        <div class="conversation-product">
                            <?php echo htmlspecialchars($conv['product_name']); ?>
                            <?php if ($conv['unread_count'] > 0): ?>
                            <span class="unread-badge">
                                <?php echo $conv['unread_count']; ?> új
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="conversation-user">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($conv['other_user']); ?>
                        </div>
                        <div class="conversation-time">
                            <?php echo date('Y.m.d H:i', strtotime($conv['updated_at'])); ?>
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

        <!-- Termékeim -->
        <section class="products-section">
            <div class="products-header">
                <h3 class="section-title">Termékeim</h3>
                <div class="product-status-badges">
                    <span class="product-count-badge" style="background: var(--success); color: white;">
                        <i class="fas fa-check-circle"></i> <?php echo $active_products; ?> aktív
                    </span>
                    <span class="product-count-badge" style="background: var(--neutral-500); color: white;">
                        <i class="fas fa-check"></i> <?php echo $sold_products; ?> eladott
                    </span>
                </div>
            </div>
            
            <?php if (count($user_products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($user_products as $product): ?>
                <!-- JAVÍTOTT: product.php helyett product_detail.php -->
                <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="product-card-link">
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image"
                             onerror="this.src='./images/no-image.png'">
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                            <?php if ($product['price']): ?>
                            <div class="product-price"><?php echo number_format($product['price'], 0, ',', ' '); ?> Ft</div>
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
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="shop.php?my_products=1" class="profile-btn profile-btn-secondary">
                    <i class="fas fa-list"></i> Összes termék megtekintése
                </a>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>Még nem tettél fel terméket</p>
                <a href="create_product.php" class="profile-btn profile-btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> Új termék feladása
                </a>
            </div>
            <?php endif; ?>
        </section>

        <!-- Értékelések -->
        <?php if (count($reviews) > 0): ?>
        <section class="reviews-section">
            <h3 class="section-title">Legutóbbi értékelések</h3>
            <?php foreach ($reviews as $review): ?>
            <div class="review-item">
                <div class="review-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?php echo $i > $review['rating'] ? '-o' : ''; ?>"></i>
                    <?php endfor; ?>
                    <span style="margin-left: 0.5rem; font-weight: 600;"><?php echo $review['rating']; ?>/5</span>
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
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="reviews.php?user_id=<?php echo $current_user_id; ?>" class="profile-btn profile-btn-secondary">
                    <i class="fas fa-star"></i> Összes értékelés
                </a>
            </div>
        </section>
        <?php endif; ?>
    </main>
</div>

<script>
function confirmLogout() {
    if (confirm('Biztosan ki szeretnél jelentkezni?')) {
        window.location.href = './app/logout.php';
    }
}

function confirmDeleteAccount() {
    if (confirm('⚠️ Figyelem! A fiók törlésével minden adatod véglegesen törlődik.\n\nBiztosan folytatod?')) {
        window.location.href = './app/delete_account.php';
    }
}

// Automatikus beszélgetés frissítés (csak az oldal fókuszálásakor)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Oldal újra aktív lett, lehet frissíteni a beszélgetéseket
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
});
</script>
</body>
</html>
<?php $conn->close(); ?>