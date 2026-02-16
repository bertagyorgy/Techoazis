<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

// ======= TOP TÉMÁK (legtöbb poszt) - q-val szűrhető név alapján =======
$top_limit = 10;

$q = trim($_GET['q'] ?? '');
$group_id = isset($_GET['group']) ? (int)$_GET['group'] : 0;
$q_like = '%' . $q . '%';

if ($q !== '') {
    $stmtGroups = $conn->prepare("
        SELECT 
            g.group_id,
            g.group_name,
            COUNT(p.post_id) AS post_count
        FROM groups g
        LEFT JOIN posts p ON p.group_id = g.group_id
        WHERE g.group_name LIKE ?
        GROUP BY g.group_id, g.group_name
        ORDER BY post_count DESC, g.group_name ASC
        LIMIT $top_limit
    ");
    $stmtGroups->bind_param('s', $q_like);
    $stmtGroups->execute();
    $groups_result = $stmtGroups->get_result();
} else {
    $groups_result = $conn->query("
        SELECT 
            g.group_id,
            g.group_name,
            COUNT(p.post_id) AS post_count
        FROM groups g
        LEFT JOIN posts p ON p.group_id = g.group_id
        GROUP BY g.group_id, g.group_name
        ORDER BY post_count DESC, g.group_name ASC
        LIMIT $top_limit
    ");
}


// ======= LEGÚJABB POSZTOK JOBB OLDALRA =======
$latest_query = "
    SELECT 
        p.post_id, 
        p.title, 
        p.created_at, 
        g.group_id AS group_id,
        g.group_name AS group_name
    FROM posts p
    JOIN groups g ON p.group_id = g.group_id
    ORDER BY p.created_at DESC
    LIMIT 6
";
$latest_posts = $conn->query($latest_query);

// ======= KÖZÉPSŐ RÉSZ – POSZTOK MINDEN CSOPORTBÓL =======
if ($q !== '') {
    $stmt = $conn->prepare("
        SELECT p.*, u.username, u.username_slug AS user_slug, g.group_name AS group_name
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        JOIN groups g ON p.group_id = g.group_id
        WHERE (p.title LIKE ? OR p.content LIKE ? OR g.group_name LIKE ? OR u.username LIKE ?)
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param("ssss", $q_like, $q_like, $q_like, $q_like);
    $stmt->execute();
    $posts_result = $stmt->get_result();
} else {
    $posts_result = $conn->query("
        SELECT p.*, u.username, u.username_slug AS user_slug, g.group_name AS group_name
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        JOIN groups g ON p.group_id = g.group_id
        ORDER BY p.created_at DESC
    ");
}


// ======= ÖSSZES KÉP LEKÉRÉSE EGYBŐL =======
$images_query = "SELECT post_id, image_path FROM post_images WHERE post_id IN (
    SELECT post_id FROM posts
) ORDER BY post_id";

$images_result = $conn->query($images_query);

// HIBAKERESÉS: Ha a lekérdezés sikertelen, írja ki miért
if (!$images_result) {
    die("Hiba a képek lekérdezésekor: " . $conn->error . "<br>Lekérdezés: " . $images_query);
}

$post_images = [];
while ($img = $images_result->fetch_assoc()) {
    $post_images[$img['post_id']][] = $img['image_path'];
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Beszélgetés és tapasztalatcsere a Techoázis közösségben. Oszd meg véleményed vagy kérj segítséget más felhasználóktól.">
    <title>Techoázis | Közösség</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/post_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script> const APP_BASE_URL = "<?php echo BASE_URL; ?>";</script>
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>
</head>
<body>

<?php 
// JAVÍTÁS: A navbar a views mappában van
include ROOT_PATH . '/views/navbar.php'; 
?>
<section class="forum-wrapper">

    <!-- ======================
        BAL OLDALI SIDENAV
    ====================== -->
    <aside class="forum-left">
        <form method="GET" style="margin-bottom: 1rem;">
            <input
                type="text"
                class="group-search"
                name="q"
                value="<?= htmlspecialchars($q) ?>"
                placeholder="🔍 Poszt keresése..."
            >
        </form>
        
        <h3>Népszerű csoportok</h3>
        <ul class="group-list">
            <!-- ÖSSZES -->
            <li>
                <a
                    href="<?= BASE_URL ?>/pages/forum.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>"
                    class="<?= $group_id === 0 ? 'active' : '' ?>"
                >
                    Összes
                    <i class="fa-solid fa-layer-group"></i>
                    
                </a>
            </li>

            <!-- TOP CSOPORTOK (ikon nélkül) -->
            <?php while($row = $groups_result->fetch_assoc()): ?>
                <?php
                    $href =  BASE_URL . "/pages/forum_group.php?group=" . (int)$row['group_id'];
                    if ($q !== '') $href .= "&q=" . urlencode($q);
                ?>
                <li>
                    <a href="<?= $href ?>" class="<?= $group_id === (int)$row['group_id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($row['group_name']) ?>
                        <span style="font-weight:bold;right:0;">(<?= (int)$row['post_count'] ?>)</span>
                    </a>
                </li>
            <?php endwhile; ?>

        </ul>
        <a href="<?= BASE_URL ?>/pages/create_group.php" class="new_group"><i class="fa-solid fa-circle-plus"></i>Új csoport</a>
    </aside>

    <!-- ======================
            KÖZÉPSŐ POSZTOS SÁV
    ====================== -->
    <main class="forum-center">
        <?php if ($posts_result->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fa-regular fa-face-frown" style="font-size:2rem; margin-bottom:.75rem;"></i>
                <h2 style="margin:0 0 .5rem 0; color: var(--text-color);">Nincs találat</h2>
                <p style="margin:0;">Próbálj másik kulcsszót vagy válassz másik témát.</p>
            </div>
        <?php endif; ?>
        <?php while($post = $posts_result->fetch_assoc()): ?>
            <div class="post-card">

                <div class="article-meta">
                    <a class="article-badge" href="<?= BASE_URL ?>/pages/forum_group.php?group=<?= (int)$post['group_id'] ?>" style="text-decoration:none;">
                        #<?= htmlspecialchars($post['group_name']) ?>
                    </a>


                    <a href="<?= BASE_URL ?>/pages/profile?u=<?= urlencode($post['user_slug']) ?>">
                        <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['username']) ?></span>
                    </a>

                    <span>
                        <i class="fa-regular fa-calendar"></i>
                        <?= substr($post['created_at'], 0, 16) ?>
                    </span>
                </div>


                <h2 class="post-title"><?= htmlspecialchars($post['title']) ?></h2>

                <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                <?php
                // ===== KÉPEK MEGJELENÍTÉSE =====
                if (isset($post_images[$post['post_id']]) && !empty($post_images[$post['post_id']])): ?>
                    <div class="post-images">
                        <?php foreach ($post_images[$post['post_id']] as $image_path): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($image_path) ?>" alt="Poszt kép" class="post-image js-zoomable">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                

                <button class="show-comments-btn" data-post="<?= $post['post_id'] ?>">
                    Kommentek
                    <span class="comment-count" id="comment-count-<?= $post['post_id'] ?>">0</span>
                    <i class="fa-solid fa-caret-down comment-caret"></i>
                </button>


                <div class="comments-container" id="comments-<?= $post['post_id'] ?>"></div>

                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <form class="comment-form" data-post="<?= $post['post_id'] ?>">
                    <textarea placeholder="Írj kommentet..." maxlength="1500" required></textarea>
                    <button class="forum-submit-btn" type="submit">Küldés</button>
                </form>
                <?php else: ?>
                    <p class="login-warning">Jelentkezz be, hogy kommentelhess!</p>
                <?php endif; ?>

            </div>
        <?php endwhile; ?>

    </main>


    <!-- ======================
       JOBB OLDALI SIDENAV
    ====================== -->
    <aside class="forum-right">
        <h3>Legújabb posztok</h3>

        <?php while($lp = $latest_posts->fetch_assoc()): ?>
            <div class="latest-post-item">
                <a href="<?= BASE_URL ?>/pages/forum_group.php?group=<?= (int)$lp['group_id'] ?>&q=<?= urlencode($lp['title']) ?>">
                    <strong><?= htmlspecialchars($lp['title']) ?></strong>
                </a>
                <p class="latest-post-meta">
                    #<?= htmlspecialchars($lp['group_name']) ?> • 
                    <?= substr($lp['created_at'], 0, 16) ?>
                </p>
            </div>
        <?php endwhile; ?>
    </aside>

</section>

<dialog id="imgModal" class="img-modal">
  <button class="img-modal-close" aria-label="Bezárás">x</button>
  <img id="imgModalImage" class="img-modal-image" alt="Nagy kép">
</dialog>
<?php include ROOT_PATH . '/views/footer.php';?>
</body>
</html>
