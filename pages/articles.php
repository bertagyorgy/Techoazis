<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

/* =========================
   BEJELENTKEZETT USER ADATAI
========================= */
$user = null;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $u_res = $conn->query("SELECT user_id, user_role FROM users WHERE user_id = $uid LIMIT 1");
    if ($u_res && $u_res->num_rows > 0) {
        $user = $u_res->fetch_assoc();
    }
}

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
    SELECT category_id, category_name
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
   KÖZÉP: CIKKEK LISTA
========================= */
$sql = "
    SELECT 
        a.article_id, a.title, a.summary, a.created_at, a.reading_minutes, a.cover_image,
        u.username, u.username_slug AS author_slug, u.profile_image,
        c.category_name
    FROM articles a
    JOIN users u ON a.author_user_id = u.user_id
    JOIN article_categories c ON a.category_id = c.category_id
    WHERE a.article_status = 'published'
";

$params = [];
$types = "";
if ($category_id > 0) { $sql .= " AND a.category_id = ? "; $types .= "i"; $params[] = $category_id; }
if ($q !== '') { $sql .= " AND (a.title LIKE ? OR a.summary LIKE ? OR a.content LIKE ?) "; $types .= "sss"; $params[] = $q_like; $params[] = $q_like; $params[] = $q_like; }

$sql .= " ORDER BY a.created_at DESC";
$stmt = $conn->prepare($sql);
if ($types !== "") { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tech cikkek és útmutatók a Techoázison: hardver/szoftver tesztek, tippek és magyarázatok érthetően.">
    <title>Techoázis | Tudástár</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/create_post.css"> <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/post_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/articles_style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/group_view.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>


</head>
<body>

<?php include ROOT_PATH . '/views/navbar.php'; ?>

<section class="forum-wrapper">

    <!-- BAL: témák + kereső -->
    <aside class="forum-left">
        <form method="GET" style="margin-bottom: 1rem;">
            <input type="text" class="group-search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="🔍 Cikk keresése...">
            <?php if ($category_id > 0): ?>
                <input type="hidden" name="category" value="<?= (int)$category_id ?>">
            <?php endif; ?>
        </form>

        <h3>Kategóriák</h3>
        <ul class="group-list">
            <li>
                <a href="<?= BASE_URL ?>/pages/articles.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="<?= $category_id === 0 ? 'active' : '' ?>">
                    Összes <i class="fa-solid fa-layer-group"></i>
                </a>
            </li>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="<?= BASE_URL ?>/pages/articles.php?category=<?= (int)$cat['category_id'] ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="<?= $category_id === (int)$cat['category_id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="sidebar-actions" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1.5rem;">
            <?php if(isset($user) && isset($user['user_role']) && $user['user_role'] === 'A' || $user['user_role'] === 'U'): ?>
                <button class="display-btn" style="width: 100%; margin: 0; padding: 0.8rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fa-solid fa-plus"></i> Új cikk
                </button>
            <?php endif; ?>
        </div>
    </aside>

    <!-- KÖZÉP: cikkek -->
    <main class="forum-center">

        <?php if($user && isset($user['user_role']) && $user['user_role'] === 'A' || $user['user_role'] === 'U'): ?>

            <div class="create-post-bar"> <form action="<?= BASE_URL ?>/actions/create_article.php" method="POST" enctype="multipart/form-data">
                    
                    <label for="category_id">Célkategória:</label>
                    <select name="category_id" id="category_id" required style="width:100%; padding:0.8rem; margin-bottom:1rem; background: var(--input-bg); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 8px;">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="title">Cikk címe:</label>
                    <input type="text" name="title" id="title" placeholder="A cikk címe..." required>

                    <label for="summary">Rövid összefoglaló:</label>
                    <textarea name="summary" id="summary" placeholder="Rövid leírás a listához..." style="height: 80px;"></textarea>

                    <label for="content">Cikk tartalma:</label>
                    <textarea name="content" id="content" placeholder="Írd meg a teljes cikket..." required style="min-height: 250px;"></textarea>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1rem; align-items: end;">
                        <div class="file-inputs">
                            <label for="cover_image" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                                <i class="fa-solid fa-image"></i> Borítókép kiválasztása
                            </label>
                            <input type="file" id="cover_image" name="cover_image" accept="image/*" style="width: 100%;">
                        </div>

                        <div class="reading-time-input">
                            <label for="reading_minutes">Olvasási idő (perc)</label>

                            <input type="number" 
                                name="reading_minutes" 
                                id="reading_minutes" 
                                value="5" 
                                min="1" 
                                style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-color); font-family: 'Inter', sans-serif;">
                        </div>
                    </div>

                    <button type="submit" class="create-post-btn">Cikk publikálása</button>
                </form>
            </div>
        <?php endif; ?>

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
                        <img class="article-cover" src="<?= htmlspecialchars(BASE_URL . "/". $a['cover_image']) ?>" alt="Borítókép">
                    <?php endif; ?>
                    
                    <div class="article-body">
                        <div class="article-meta">
                            <span class="article-badge">#<?= htmlspecialchars($a['category_name']) ?></span>
                            <span class="post-date">
                                <i class="fa-regular fa-clock"></i> <?= (int)$a['reading_minutes'] ?> perc&nbsp;
                                <i class="fa-regular fa-calendar"></i> <?= substr($a['created_at'], 0, 10) ?>
                            </span>
                        </div>

                        <h2 class="article-title"><?= htmlspecialchars($a['title']) ?></h2>
                        <p class="article-summary"><?= nl2br(htmlspecialchars($a['summary'])) ?></p>

                        <div class="article-actions">
                            <a class="read-btn" href="<?= BASE_URL ?>/pages/article_detail.php?id=<?= (int)$a['article_id'] ?>">
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
                <a href="<?= BASE_URL ?>/pages/article_detail.php?id=<?= (int)$la['article_id'] ?>">
                    <strong><?= htmlspecialchars($la['title']) ?></strong>
                </a>
                <p class="latest-post-meta">#<?= htmlspecialchars($la['category_name']) ?></p>
            </div>
        <?php endforeach; ?>
    </aside>

</section>

<?php include ROOT_PATH . '/views/footer.php';?>
</body>
</html>