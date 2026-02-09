<?php
session_start();
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/app/db.php';

/* =========================
   GET PARAMS
========================= */
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$q = trim($_GET['q'] ?? '');
$q_like = '%' . $q . '%';

/* =========================
   BAL OLDAL: KATEGÓRIÁK
========================= */
$categories = [];
$res = $conn->query("
    SELECT category_id, category_name, icon_class
    FROM article_categories
    ORDER BY sort_order ASC, category_name ASC
");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}

/* =========================
   JOBB OLDAL: LEGÚJABB CIKKEK
========================= */
$latest_stmt = $conn->prepare("
    SELECT a.article_id, a.title, a.created_at, c.category_name
    FROM articles a
    JOIN article_categories c ON a.category_id = c.category_id
    WHERE a.article_status = 'published'
    ORDER BY a.created_at DESC
    LIMIT 6
");
$latest_stmt->execute();
$latest_articles = $latest_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$latest_stmt->close();

/* =========================
   KÖZÉP: CIKKEK LISTA (filter + search)
========================= */
$sql = "
    SELECT 
        a.article_id,
        a.title,
        a.summary,
        a.created_at,
        a.reading_minutes,
        a.cover_image,
        u.username,
        u.username_slug AS author_slug,
        c.category_name
    FROM articles a
    JOIN users u ON a.author_user_id = u.user_id
    JOIN article_categories c ON a.category_id = c.category_id
    WHERE a.article_status = 'published'
";

$params = [];
$types = "";

if ($category_id > 0) {
    $sql .= " AND a.category_id = ? ";
    $types .= "i";
    $params[] = $category_id;
}

if ($q !== '') {
    $sql .= " AND (a.title LIKE ? OR a.summary LIKE ? OR a.content LIKE ?) ";
    $types .= "sss";
    $params[] = $q_like;
    $params[] = $q_like;
    $params[] = $q_like;
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | Tudástár</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/post_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/articles_style.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>


</head>
<body>

<?php include ROOT_PATH . '/views/navbar.php'; ?>

<section class="forum-wrapper">

    <!-- BAL: témák + kereső -->
    <aside class="forum-left">
        <form method="GET" style="margin-bottom: 1rem;">
            <input
                type="text"
                class="group-search"
                name="q"
                value="<?= htmlspecialchars($q) ?>"
                placeholder="🔍 Cikk keresése..."
            >
            <?php if ($category_id > 0): ?>
                <input type="hidden" name="category" value="<?= (int)$category_id ?>">
            <?php endif; ?>
        </form>

        <h3>Cikkek</h3>
        <ul class="group-list">
            <li>
                <a href="<?= BASE_URL ?>/articles.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>"
                   class="<?= $category_id === 0 ? 'active' : '' ?>">
                    Összes
                    <i class="fa-solid fa-layer-group"></i>
                    
                </a>
            </li>

            <?php foreach ($categories as $cat): ?>
                <?php
                    $href = BASE_URL . '/articles.php?category=' . (int)$cat['category_id'];
                    if ($q !== '') $href .= "&q=" . urlencode($q);
                ?>
                <li>
                    <a href="<?= $href ?>" class="<?= $category_id === (int)$cat['category_id'] ? 'active' : '' ?>">
                        <!--<i class="<= htmlspecialchars($cat['icon_class'] ?: 'fa-solid fa-hashtag') ?>"></i>-->
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- KÖZÉP: cikkek -->
    <main class="forum-center">
        <?php if (empty($articles)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-face-frown" style="font-size:2rem; margin-bottom:.75rem;"></i>
                <h2 style="margin:0 0 .5rem 0; color: var(--text-color);">Nincs találat</h2>
                <p style="margin:0;">Próbálj másik kulcsszót vagy válassz másik témát.</p>
            </div>
        <?php else: ?>
            <?php foreach ($articles as $a): ?>
                <article class="article-card">
                    <?php if (!empty($a['cover_image'])): ?>
                        <img class="article-cover" src="<?= htmlspecialchars(BASE_URL . "/". $a['cover_image']) ?>" alt="Cikk borítókép">
                    <?php endif; ?>

                    <div class="article-body">
                        <div class="article-meta">
                            <span class="article-badge">#<?= htmlspecialchars($a['category_name']) ?></span>
                            <a href="<?= BASE_URL ?>/profile?u=<?= urlencode($a['author_slug']) ?>">
                                <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($a['username']) ?></span>
                            </a>
                            <span><i class="fa-regular fa-clock"></i>
                                <?= $a['reading_minutes'] ? (int)$a['reading_minutes'] . " perc" : "—" ?>
                            </span>
                            <span><i class="fa-regular fa-calendar"></i> <?= substr($a['created_at'], 0, 16) ?></span>
                        </div>

                        <h2 class="article-title"><?= htmlspecialchars($a['title']) ?></h2>

                        <?php if (!empty($a['summary'])): ?>
                            <p class="article-summary"><?= nl2br(htmlspecialchars($a['summary'])) ?></p>
                        <?php endif; ?>

                        <div class="article-actions">
                            <a class="read-btn" href="<?= BASE_URL ?>/article_detail.php?id=<?= (int)$a['article_id'] ?>">
                                <i class="fa-solid fa-book-open"></i> Elolvasom
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- JOBB: legújabb -->
    <aside class="forum-right">
        <h3>Legújabb cikkek</h3>

        <?php foreach ($latest_articles as $la): ?>
            <div class="latest-post-item">
                <a href="<?= BASE_URL ?>/article_detail.php?id=<?= (int)$la['article_id'] ?>">
                    <strong><?= htmlspecialchars($la['title']) ?></strong>
                </a>
                <p class="latest-post-meta">
                    #<?= htmlspecialchars($la['category_name']) ?> • <?= substr($la['created_at'], 0, 16) ?>
                </p>
            </div>
        <?php endforeach; ?>
    </aside>

</section>

</body>
</html>
