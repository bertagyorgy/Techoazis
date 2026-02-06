<?php
session_start();
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/app/db.php';

if (!isset($_GET['group'])) {
    echo "<script>window.location.href='" . BASE_URL . "/forum.php';</script>";
    exit();
}

$group_id = intval($_GET['group']);
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    $sql = "
        SELECT p.*
        FROM posts p
        WHERE p.group_id = ?
        ORDER BY (p.title = ?) DESC, p.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $group_id, $q);
} else {
    $sql = "
        SELECT p.*
        FROM posts p
        WHERE p.group_id = ?
        ORDER BY p.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);
}

// ===== CSOPORT ADATOK =====
$group_query = "SELECT group_name, group_image, group_description FROM groups WHERE group_id = ?";
$stmt = $conn->prepare($group_query);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group_result = $stmt->get_result();
$group = $group_result->fetch_assoc();

if (!$group) {
    echo "<h2>A csoport nem található!</h2>";
    exit();
}

// ===== POSZTOK LEKÉRÉSE =====
$posts_query = "
    SELECT p.*, u.username, u.username_slug AS user_slug
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.group_id = ?
    ORDER BY p.created_at DESC
";
$stmt2 = $conn->prepare($posts_query);
$stmt2->bind_param("i", $group_id);
$stmt2->execute();
$posts = $stmt2->get_result();

// Összes poszt szám
$post_count = $posts->num_rows;

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | <?= htmlspecialchars($group['group_name']) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/comments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/create_post.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/group_view.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/post_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/container&grid_system.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script> const APP_BASE_URL = "<?php echo BASE_URL; ?>";</script>
    <script src="<?= BASE_URL ?>/static/index.js" defer></script>
    <script src="<?= BASE_URL ?>/static/forum.js" defer></script>
</head>
<body>

<?php include ROOT_PATH . '/views/navbar.php'; ?>

<section class="group-wrapper">

    <!-- ==== CSOPORT HEADER ==== -->
    <div class="group-header">
        <img src="<?= BASE_URL ?>/uploads/groups/<?= htmlspecialchars($group['group_image']) ?>" class="group-banner">
        <div class="group-header-info">
            <h1>#<?= htmlspecialchars($group['group_name']) ?></h1>
            <p class="group-meta"><?= $post_count ?> poszt ebben a csoportban</p>
            <h4>Leírás:</h4><p class="group-meta"><?= htmlspecialchars($group['group_description']) ?></p>
        </div>
    </div>
    <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <div class="btn-section">
        <button class="display-btn"><i class="fa-solid fa-plus"></i> Új poszt</button>
        <a href="<?= BASE_URL ?>/forum.php" class="display-btn back">Vissza a fórumhoz</a> 
    </div>
    <div class="create-post-bar">
        <form action="<?= BASE_URL ?>/create_post.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="group_id" value="<?= $group_id ?>">

            <label for="title">Cím:</label>
            <input type="text" name="title" id="title" placeholder="Poszt címe..." required>

            <label for="content">Tartalom:</label>
            <textarea name="content" id="content" placeholder="Írd meg a poszt tartalmát..." required></textarea>

            <div class="file-inputs">
                <label for="postImages">Képek feltöltése (max 3)</label>
                <input type="file" id="postImages" name="images[]" accept="image/*" multiple>
            </div>
            <div id="imagePreview"></div>

            <button type="submit" class="create-post-btn">Poszt létrehozása</button>
        </form>
    </div>
    <?php else: ?>
    <div class="btn-section">
        <a href="<?= BASE_URL ?>/forum.php" class="display-btn back">Vissza a fórumhoz</a>
    </div>
    <?php endif; ?>

    <!-- ==== POSZTOK ==== -->
    <main class="forum-posts">
        <?php if ($post_count === 0): ?>
            <div class="empty-state">
                <i class="fa-regular fa-face-frown" style="font-size:2rem; margin-bottom:.75rem;"></i>
                <h2 style="margin:0 0 .5rem 0; color: var(--text-color);">Nincs találat</h2>
                <p style="margin:0;">Próbálj másik kulcsszót vagy válassz másik témát.</p>
            </div>
        <?php endif; ?>
        
        <?php while($post = $posts->fetch_assoc()): ?>
            <div class="post-card">

                <div class="article-meta">
                    <a class="article-badge" href="<?= BASE_URL ?>/forum_group.php?group=<?= (int)$post['group_id'] ?>" style="text-decoration:none;">
                        #<?= htmlspecialchars($group['group_name']) ?>
                    </a>

                    <a href="<?= BASE_URL ?>/profile?u=<?= urlencode($post['user_slug']) ?>">
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
                // ===== KÉPEK LEKÉRÉSE =====
                $img_stmt = $conn->prepare("SELECT image_path FROM images WHERE post_id = ?");
                $img_stmt->bind_param("i", $post['post_id']);
                $img_stmt->execute();
                $images = $img_stmt->get_result();

                if ($images->num_rows > 0): ?>
                    <div class="post-images">
                        <?php while ($img = $images->fetch_assoc()): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img['image_path']) ?>" class="post-image">
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                

                <button class="show-comments-btn" data-post="<?= $post['post_id'] ?>">
                    Kommentek
                    <span class="comment-count" id="comment-count-<?= $post['post_id'] ?>">0</span>
                    <i class="fa-solid fa-caret-down comment-caret"></i>
                </button>


                <div class="comments-container" id="comments-<?= $post['post_id'] ?>"></div>

                <?php if(isset($_SESSION['loggedin'])): ?>
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

</section>
</body>
</html>
