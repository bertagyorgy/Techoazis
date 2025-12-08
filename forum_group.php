<?php
session_start();
include './app/db.php';

if (!isset($_GET['group'])) {
    echo "<script>window.location.href='../forum.php';</script>";
    exit();
}

$group_id = intval($_GET['group']);

// ===== CSOPORT ADATOK =====
$group_query = "SELECT group_name, group_image FROM groups WHERE group_id = ?";
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
    SELECT p.*, u.username
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
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <title><?= htmlspecialchars($group['group_name']) ?> | Techoazis</title>
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
</head>
<body>

<?php include './views/navbar.php'; ?>

<section class="group-wrapper">

    <!-- ==== CSOPORT HEADER ==== -->
    <div class="group-header">
        <img src="./uploads/groups/<?= htmlspecialchars($group['group_image']) ?>" class="group-banner">
        <div class="group-header-info">
            <h1>#<?= htmlspecialchars($group['group_name']) ?></h1>
            <p class="group-meta"><?= $post_count ?> poszt ebben a csoportban</p>
        </div>
    </div>
    <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <div class="create-post-bar">
        <form action="create_post.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="group_id" value="<?= $group_id ?>">

            <input type="text" name="title" placeholder="Poszt címe..." required>

            <textarea name="content" placeholder="Írd meg a poszt tartalmát..." required></textarea>

            <div class="file-inputs">
                <label>Képek feltöltése (max 3):</label>
                <input type="file" id="postImages" name="images[]" accept="image/*" multiple>
            </div>
            <div id="imagePreview"></div>

            <button type="submit" class="create-post-btn">Poszt létrehozása</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- ==== POSZTOK ==== -->
    <main class="group-posts">
        <?php if ($post_count === 0): ?>
            <p class="no-posts">Ebben a csoportban még nincs poszt.</p>
        <?php endif; ?>

        <?php while($post = $posts->fetch_assoc()): ?>
            <div class="post-card">

                <div class="post-header">
                    <span class="post-user"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($post['username']) ?></span>
                    <span class="post-date"><?= $post['created_at'] ?></span>
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
                            <img src="./<?= htmlspecialchars($img['image_path']) ?>" class="post-image">
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>


                <button class="show-comments-btn" data-post="<?= $post['post_id'] ?>">
                    Kommentek megnyitása
                </button>

                <div class="comments-container" id="comments-<?= $post['post_id'] ?>"></div>

                <?php if(isset($_SESSION['loggedin'])): ?>
                <form class="comment-form" data-post="<?= $post['post_id'] ?>">
                    <textarea placeholder="Írj kommentet..." maxlength="800" required></textarea>
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
