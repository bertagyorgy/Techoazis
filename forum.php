<?php
session_start();
include './app/db.php';

// ======= CSOPORTOK LEKÉRÉSE =======
$groups_query = "SELECT group_id, group_name FROM groups ORDER BY group_name ASC";
$groups_result = $conn->query($groups_query);

// ======= LEGÚJABB POSZTOK JOBB OLDALRA =======
$latest_query = "
    SELECT p.post_id, p.title, p.created_at, g.group_name AS group_name 
    FROM posts p
    JOIN groups g ON p.group_id = g.group_id
    ORDER BY p.created_at DESC
    LIMIT 6
";
$latest_posts = $conn->query($latest_query);

// ======= KÖZÉPSŐ RÉSZ – POSZTOK MINDEN CSOPORTBÓL =======
$posts_query = "
    SELECT p.*, u.username, g.group_name AS group_name
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    JOIN groups g ON p.group_id = g.group_id
    ORDER BY p.created_at DESC
";
$posts_result = $conn->query($posts_query);

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoazis | Community</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/index.js" defer></script>
    <script src="./static/forum.js" defer></script>
</head>
<body>

<?php include './views/navbar.php'; ?>

<section class="forum-wrapper">

    <!-- ======================
        BAL OLDALI SIDENAV
    ====================== -->
    <aside class="forum-left">
        <input type="text" class="group-search" placeholder="🔍 Csoport keresése...">

        <ul class="group-list">
            <?php while($row = $groups_result->fetch_assoc()): ?>
                <li>
                    <a href="forum_group.php?group=<?= $row['group_id'] ?>">
                        <?= htmlspecialchars($row['group_name']) ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </aside>


    <!-- ======================
            KÖZÉPSŐ POSZTOS SÁV
    ====================== -->
    <main class="forum-center">

        <?php while($post = $posts_result->fetch_assoc()): ?>
            <div class="post-card">

                <div class="post-header">
                    <span class="post-group">#<?= htmlspecialchars($post['group_name']) ?></span>
                    <span class="post-user">
                        <i class="fa-solid fa-user"></i> 
                        <?= htmlspecialchars($post['username']) ?>
                    </span>
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

                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <form class="comment-form" data-post="<?= $post['post_id'] ?>">
                    <textarea placeholder="Írj kommentet..." maxlength="800" required></textarea>
                    <button type="submit">Küldés</button>
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
                <a href="post.php?id=<?= $lp['post_id'] ?>">
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

</body>
</html>
