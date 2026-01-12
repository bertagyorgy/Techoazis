<?php
session_start();
require_once __DIR__ . '/app/db.php';

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($article_id <= 0) {
    header("Location: articles.php");
    exit();
}

// Cikk lekérése (csak published)
$stmt = $conn->prepare("
    SELECT
        a.article_id,
        a.title,
        a.summary,
        a.content,
        a.cover_image,
        a.reading_minutes,
        a.status,
        a.created_at,
        a.updated_at,
        c.category_id,
        c.category_name,
        c.icon_class,
        u.user_id AS author_id,
        u.username AS author_username,
        u.profile_image AS author_image
    FROM articles a
    JOIN article_categories c ON a.category_id = c.category_id
    JOIN users u ON a.author_user_id = u.user_id
    WHERE a.article_id = ?
      AND a.status = 'published'
    LIMIT 1
");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$article) {
    header("Location: articles.php");
    exit();
}

// Dátum formázás
$published_date = date('Y. m. d. H:i', strtotime($article['created_at']));
$updated_date = $article['updated_at'] ? date('Y. m. d. H:i', strtotime($article['updated_at'])) : null;

// Ha a content nálad HTML-t tartalmaz (pl. <b>), akkor a htmlspecialchars helyett whitelistes sanitizer kell.
// Most biztonságos: sima szövegként rendereljük.
function render_text($text) {
    return nl2br(htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> | Techoázis Tudástár</title>

    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/responsive_adjustments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="./static/index.js" defer></script>
    <script src="./static/forum.js" defer></script>
    <style>
        .article-wrap{
            max-width: 980px;
            margin: calc(var(--navbar-height) + 2rem) auto 3rem auto;
            padding: 0 1.25rem;
        }

        .article-card{
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .article-cover{
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }

        .article-body{
            padding: 1.75rem;
        }

        .article-top{
            display:flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .crumbs a{
            color: var(--text-light);
            text-decoration: none;
        }
        .crumbs a:hover{ text-decoration: underline; }

        .badge{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: var(--primary-100);
            color: var(--primary-700);
            font-weight: 700;
            font-size: .9rem;
        }

        .article-title{
            margin: 0 0 .75rem 0;
            color: var(--text-color);
            line-height: 1.15;
            font-size: clamp(1.8rem, 3.2vw, 2.6rem);
        }

        .meta-row{
            display:flex;
            flex-wrap: wrap;
            gap: .9rem;
            color: var(--text-light);
            font-size: .95rem;
            margin-bottom: 1.25rem;
        }

        .author{
            display:flex;
            align-items:center;
            gap:.75rem;
        }
        .author img{
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border-color);
        }

        .summary{
            background: var(--background);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-md);
            padding: 1rem 1.1rem;
            color: var(--text-color);
            line-height: 1.65;
            margin-bottom: 1.5rem;
        }

        .content{
            color: var(--text-color);
            line-height: 1.85;
            font-size: 1.05rem;
            word-wrap: break-word;
        }

        .content code, .content pre{
            background: rgba(0,0,0,.06);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-md);
        }

        .footer-actions{
            display:flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-light);
        }

        .back-btn{
            display:inline-flex;
            align-items:center;
            gap:.6rem;
            padding: .7rem 1rem;
            border-radius: var(--border-radius-md);
            background: linear-gradient(45deg, var(--accent-600), var(--accent-400));
            color: white;
            font-weight: 800;
            text-decoration: none;
        }
        .back-btn:hover{
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        @media (max-width: 768px){
            .article-cover{ height: 220px; }
            .article-body{ padding: 1.25rem; }
        }
    </style>
</head>

<body>
<?php include './views/navbar.php'; ?>

<div class="article-wrap">
    <div class="article-card">
        <?php if (!empty($article['cover_image'])): ?>
            <img class="article-cover" src="<?= htmlspecialchars($article['cover_image']) ?>" alt="Cikk borítókép">
        <?php endif; ?>

        <div class="article-body">
            <div class="article-top">
                <div class="crumbs">
                    <a href="articles.php"><i class="fa-solid fa-arrow-left"></i> Tudástár</a>
                </div>
                <div class="badge">
                    #<?= htmlspecialchars($article['category_name']) ?>
                </div>
            </div>

            <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>

            <div class="meta-row">
                <span><i class="fa-regular fa-calendar"></i> <?= $published_date ?></span>
                <?php if (!empty($article['reading_minutes'])): ?>
                    <span><i class="fa-regular fa-clock"></i> <?= (int)$article['reading_minutes'] ?> perc</span>
                <?php endif; ?>
                <?php if ($updated_date): ?>
                    <span><i class="fa-solid fa-rotate"></i> Frissítve: <?= $updated_date ?></span>
                <?php endif; ?>
            </div>

            <div class="author" style="margin-bottom: 1.25rem;">
                <img src="<?= htmlspecialchars($article['author_image'] ?: 'images/anonymous.png') ?>" alt="Szerző">
                <div>
                    <div style="color: var(--text-color); font-weight: 800;">
                        <?= htmlspecialchars($article['author_username']) ?>
                    </div>
                    <div style="color: var(--text-light); font-size: .9rem;">
                        Szerző
                    </div>
                </div>
            </div>

            <?php if (!empty($article['summary'])): ?>
                <div class="summary">
                    <strong>Röviden:</strong><br>
                    <?= render_text($article['summary']) ?>
                </div>
            <?php endif; ?>

            <div class="content">
                <?= render_text($article['content']) ?>
            </div>

            <div class="footer-actions">
                <a class="back-btn" href="articles.php">
                    <i class="fa-solid fa-layer-group"></i> Vissza a cikkekhez
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
