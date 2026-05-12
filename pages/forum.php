<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

// ======= ADATOK LEKÉRÉSE A FORMHOZ ÉS KERESÉSHEZ =======
$q = trim($_GET['q'] ?? '');
$group_id_param = isset($_GET['group']) ? (int)$_GET['group'] : 0;
$q_like = '%' . $q . '%';

// Csoportok listája a sidenavhoz és a legördülő menühöz
$groups_all = [];
$res_groups = $conn->query("SELECT group_id, group_name FROM groups ORDER BY group_name ASC");
while($g = $res_groups->fetch_assoc()) {
    $groups_all[] = $g;
}

// ======= KÖZÉPSŐ RÉSZ – POSZTOK LEKÉRÉSE =======
if ($q !== '') {
    $stmt = $conn->prepare("
        SELECT p.*, u.username, u.username_slug AS user_slug, u.profile_image, g.group_name
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
        SELECT p.*, u.username, u.username_slug AS user_slug, u.profile_image, g.group_name
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        JOIN groups g ON p.group_id = g.group_id
        ORDER BY p.created_at DESC
    ");
}

// ======= LEGÚJABB POSZTOK JOBB OLDALRA =======
$latest_posts = $conn->query("
    SELECT p.post_id, p.title, p.created_at, g.group_id, g.group_name
    FROM posts p
    JOIN groups g ON p.group_id = g.group_id
    ORDER BY p.created_at DESC LIMIT 6
");
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/create_post.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/post_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/group_view.css">

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
            <input type="text" class="group-search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="🔍 Poszt keresése...">
        </form>
        
        <h3>Népszerű csoportok</h3>
        <ul class="group-list">
            <li>
                <a href="<?= BASE_URL ?>/pages/forum.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="<?= $group_id_param === 0 ? 'active' : '' ?>">
                    Összes <i class="fa-solid fa-layer-group"></i>
                </a>
            </li>
            <?php foreach($groups_all as $g): ?>
                <li>
                    <a href="<?= BASE_URL ?>/pages/forum_group.php?group=<?= (int)$g['group_id'] ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">
                        <?= htmlspecialchars($g['group_name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="sidebar-actions" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1.5rem;">
            <a href="<?= BASE_URL ?>/pages/create_group.php" class="new_group" style="width: 100%; margin: 0; text-align: center; box-sizing: border-box;">
                <i class="fa-solid fa-circle-plus"></i> Új csoport
            </a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <button class="display-btn" style="width: 100%; margin: 0; padding: 0.8rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fa-solid fa-plus"></i> Új poszt
                </button>
            <?php endif; ?>
        </div>
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

        <?php if(isset($_SESSION['user_id'])): ?>


            <div class="create-post-bar">
                <form action="<?= BASE_URL ?>/actions/create_post.php" method="POST" enctype="multipart/form-data">
                    
                    <label for="group_id">Válassz csoportot:</label>
                    <select name="group_id" id="group_id" required style="width:100%; padding:0.8rem; margin-bottom:1rem; background: var(--input-bg); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 8px;">
                        <option value="" disabled selected>Hova posztolnál?</option>
                        <?php foreach ($groups_all as $g): ?>
                            <option value="<?= (int)$g['group_id'] ?>"><?= htmlspecialchars($g['group_name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="title">Cím:</label>
                    <input type="text" name="title" id="title" placeholder="A posztod címe..." required>

                    <label for="content">Tartalom:</label>
                    <textarea name="content" id="content" placeholder="Miről szeretnél írni?" required style="min-height: 150px;"></textarea>

                    <div class="file-inputs" style="margin-top: 1rem;">
                        <label for="postImages" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                            <i class="fa-solid fa-images"></i> Képek csatolása (max 3)
                        </label>
                        <input type="file" id="postImages" name="images[]" accept="image/*" multiple>
                    </div>
                    <div id="imagePreview"></div>

                    <button type="submit" class="create-post-btn" style="margin-top: 1.5rem;">Poszt közzététele</button>
                </form>
            </div>
        <?php endif; ?>

        <?php while($post = $posts_result->fetch_assoc()): ?>
            <div class="post-card">
                <div class="article-meta">
                    <a class="article-badge" href="<?= BASE_URL ?>/pages/forum_group.php?group=<?= (int)$post['group_id'] ?>">
                        #<?= htmlspecialchars($post['group_name']) ?>
                    </a>

                    <?php
                    $is_external = preg_match('/^https?:\/\//', $post['profile_image']);
                    $profile_avatar = !empty($post['profile_image']) 
                        ? ($is_external ? $post['profile_image'] : BASE_URL . '/' . $post['profile_image']) 
                        : BASE_URL . '/uploads/profile_images/anonymous.png';
                    ?>
                    <div class="post-meta">
                        <a href="<?= BASE_URL ?>/pages/profile?u=<?= urlencode($post['user_slug']) ?>" class="profile-link">
                            <span class="user-info">
                                <img class="profile-avatar-image" src="<?= $profile_avatar ?>" alt="<?= htmlspecialchars($post['username']) ?>">
                                <span class="username"><?= htmlspecialchars($post['username']) ?></span>
                            </span>
                        </a>
                        <span class="post-date"><i class="fa-regular fa-calendar"></i> <?= substr($post['created_at'], 0, 16) ?></span>
                    </div>
                </div>

                <h2 class="post-title"><?= htmlspecialchars($post['title']) ?></h2>
                
                <div class="text-container" id="postText-<?= $post['post_id'] ?>">
                    <?php 
                    $content = $post['content'];
                    if (mb_strlen($content, 'UTF-8') > 250): 
                        $preview = mb_substr($content, 0, 250, 'UTF-8');
                        $more = mb_substr($content, 250, null, 'UTF-8');
                    ?>
                        <?= nl2br(htmlspecialchars($preview)) ?><span class="more-content"><?= nl2br(htmlspecialchars($more)) ?></span><a href="#" class="read-more-link" onclick="toggleReadMore(event, <?= $post['post_id'] ?>)"> ...Több</a>
                    <?php else: ?>
                        <?= nl2br(htmlspecialchars($content)) ?>
                    <?php endif; ?>
                </div>
                
                <?php
                // ===== JAVÍTOTT KÉP LEKÉRDEZÉS (Így biztosan megjelenik mind a 3 kép) =====
                $img_stmt = $conn->prepare("SELECT image_path FROM post_images WHERE post_id = ? ORDER BY sort_order ASC");
                $img_stmt->bind_param("i", $post['post_id']);
                $img_stmt->execute();
                $images = $img_stmt->get_result();

                if ($images->num_rows > 0): ?>
                    <div class="post-images">
                        <?php while ($img = $images->fetch_assoc()): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img['image_path']) ?>" alt="Poszt kép" class="post-image js-zoomable">
                        <?php endwhile; ?>
                    </div>
                <?php endif; $img_stmt->close(); ?>

                <button class="show-comments-btn" data-post="<?= $post['post_id'] ?>">
                    <i style="color: white" class="fa-solid fa-comment"></i>
                    <span class="comment-count" id="comment-count-<?= $post['post_id'] ?>">0</span>
                    <i class="fa-solid fa-caret-down comment-caret"></i>
                </button>

                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <form class="comment-form" data-post="<?= $post['post_id'] ?>">
                    <textarea class="comment-input" placeholder="Írj kommentet..." maxlength="1500" required></textarea>
                    <button class="forum-submit-btn" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
                <?php else: ?>
                    <p class="login-warning">Jelentkezz be, hogy kommentelhess!</p>
                <?php endif; ?>
                
                <div class="comments-container" id="comments-<?= $post['post_id'] ?>"></div>
            </div>
        <?php endwhile; ?>
    </main>

    <aside class="forum-right">
        <h3>Legújabb posztok</h3>
        <?php while($lp = $latest_posts->fetch_assoc()): ?>
            <div class="latest-post-item">
                <a href="<?= BASE_URL ?>/pages/forum_group.php?group=<?= (int)$lp['group_id'] ?>&q=<?= urlencode($lp['title']) ?>">
                    <strong><?= htmlspecialchars($lp['title']) ?></strong>
                </a>
                <p class="latest-post-meta">#<?= htmlspecialchars($lp['group_name']) ?> • <?= substr($lp['created_at'], 0, 16) ?></p>
            </div>
        <?php endwhile; ?>
    </aside>
</section>

<dialog id="imgModal" class="img-modal">
  <button class="img-modal-close" aria-label="Bezárás">x</button>
  <img id="imgModalImage" class="img-modal-image" alt="Nagy kép">
</dialog>

<?php include ROOT_PATH . '/views/footer.php'; ?>

</body>
</html>