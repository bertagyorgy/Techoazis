<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($article_id <= 0) {
    header("Location: " . BASE_URL . "/pages/articles.php");
    exit();
}

// Cikk lekérése az adatbázisból
$stmt = $conn->prepare("
    SELECT
        a.article_id, a.title, a.summary, a.content, a.cover_image, a.reading_minutes,
        a.article_status, a.created_at, a.updated_at,
        c.category_id, c.category_name,
        u.user_id AS author_id, u.username AS author_username,
        u.profile_image AS author_image, u.username_slug AS author_slug
    FROM articles a
    JOIN article_categories c ON a.category_id = c.category_id
    JOIN users u ON a.author_user_id = u.user_id
    WHERE a.article_id = ? AND a.article_status = 'published'
    LIMIT 1
");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$article) {
    header("Location: " . BASE_URL . "/pages/articles.php");
    exit();
}

$published_date = date('Y. m. d. H:i', strtotime($article['created_at']));
$updated_date = $article['updated_at'] ? date('Y. m. d. H:i', strtotime($article['updated_at'])) : null;

function render_text($text) {
    return nl2br(htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Olvasd el a legfrissebb tech cikket a Techoázison: részletes magyarázatok és gyakorlati tanácsok hardver/szoftver témában.">
    <title>Techoázis | <?= htmlspecialchars($article['title']) ?></title>
    
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/article_detail_style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>
</head>

<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

<main class="article-wrap">
    <article class="article-card">
        <?php if (!empty($article['cover_image'])): ?>
            <div class="article-cover-wrapper">
                <img class="article-cover" src="<?= BASE_URL ?>/<?= htmlspecialchars($article['cover_image']) ?>" alt="Borítókép">
            </div>
        <?php endif; ?>

        <div class="article-body">
            <header class="article-header">
                <nav class="navigation-top">
                    <a href="<?= BASE_URL ?>/pages/articles.php" class="back-btn">
                        <i class="fa-solid fa-arrow-left-long"></i> Vissza a cikkekhez
                    </a>
                </nav>

                <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>

                <div class="meta-row">
                    <span class="badge">#<?= htmlspecialchars($article['category_name']) ?></span>
                    <span><i class="fa-regular fa-calendar"></i> <?= $published_date ?></span>
                    <?php if (!empty($article['reading_minutes'])): ?>
                        <span><i class="fa-regular fa-clock"></i> <?= (int)$article['reading_minutes'] ?> perc</span>
                    <?php endif; ?>
                    <?php if ($updated_date): ?>
                        <span><i class="fa-solid fa-rotate"></i> Frissítve: <?= $updated_date ?></span>
                    <?php endif; ?>
                </div>

                <div class="author">
                    <?php
                    $is_external = preg_match('/^https?:\/\//', $article['author_image']);
                    $author_img = !empty($article['author_image']) 
                        ? ($is_external ? $article['author_image'] : BASE_URL . '/' . $article['author_image']) 
                        : BASE_URL . '/uploads/profile_images/anonymous.png';
                    ?>
                    <img src="<?= htmlspecialchars($author_img) ?>" alt="Szerző">
                    <div class="author-info">
                        <a href="<?= BASE_URL ?>/pages/profile?u=<?= urlencode($article['author_slug']) ?>" class="author-name">
                            <?= htmlspecialchars($article['author_username']) ?>
                        </a>
                        <span class="author-role">Szerző</span>
                    </div>
                </div>
            </header>

            <?php if (!empty($article['summary'])): ?>
                <div class="summary">
                    <strong>Röviden</strong>
                    <p><?= render_text($article['summary']) ?></p>
                </div>
            <?php endif; ?>

            <div class="content">
                <?= render_text($article['content']) ?>
            </div>

            <footer class="footer-actions">
                <a href="<?= BASE_URL ?>/pages/articles.php" class="back-btn">
                    <i class="fa-solid fa-arrow-left-long"></i> Vissza a cikkekhez
                </a>
            </footer>
        </div>
    </article>
</main>
</body>
</html>