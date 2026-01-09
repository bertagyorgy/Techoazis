<?php require '../app/auth_check.php'; // ✅ JAVÍTVA: Visszalépés a gyökérbe (../)
?>


<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="../images/palmtree_favicon.svg">
    <title>Techoazis | Adminpanel</title>
    <link rel="stylesheet" href="../static/index.css">
    <link rel="stylesheet" href="../static/animations_microinteractions.css">
    <link rel="stylesheet" href="../static/button_system.css">
    <link rel="stylesheet" href="../static/modern_navbar.css">
    <link rel="stylesheet" href="../static/reset&base_styles.css">
    <link rel="stylesheet" href="../static/container&grid_system.css">
    <link rel="stylesheet" href="../static/admin-modern.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="./static/index.js" defer></script>
    <script src="./static/forum.js" defer></script>
</head>
<body>
    <div class="up-bar">
        <?php include '../views/navbar.php'; ?>
    </div>

    <div class="admin-container">
        <!-- Oldalsó menü -->
        <div class="side-nav">
            <div class="logo">
                <img src="../images/palmtree_favicon.svg" class="logo-icon">
                <p class="logo-text">Adminpanel</p>
            </div>
            <ul class="nav-links">
                <li><a href="?page=panel_dashboard" class="<?php echo ($_GET['page'] ?? '') === 'panel_dashboard' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-simple"></i><p>Statisztika</p></a></li>
                <hr class="menu-separator">
                <li><a href="?page=panel_users" class="<?php echo ($_GET['page'] ?? '') === 'panel_users' ? 'active' : ''; ?>"><i class="fa-solid fa-user-gear"></i><p>Felhasználók</p></a></li>
                <li><a href="?page=panel_login" class="<?php echo ($_GET['page'] ?? '') === 'panel_login' ? 'active' : ''; ?>"><i class="fa-solid fa-right-to-bracket"></i><p>Bejelentkezések</p></a></li>
                <li><a href="?page=panel_products" class="<?php echo ($_GET['page'] ?? '') === 'panel_products' ? 'active' : ''; ?>"><i class="fas fa-box-open"></i><p>Termékek</p></a></li>
                <li><a href="?page=panel_posts" class="<?php echo ($_GET['page'] ?? '') === 'panel_posts' ? 'active' : ''; ?>"><i class="fa-solid fa-pen-to-square"></i><p>Bejegyzések</p></a></li>
                <li><a href="?page=panel_comments" class="<?php echo ($_GET['page'] ?? '') === 'panel_comments' ? 'active' : ''; ?>"><i class="fa-solid fa-comments"></i><p>Kommentek</p></a></li>
                <li><a href="?page=panel_badges" class="<?php echo ($_GET['page'] ?? '') === 'panel_badges' ? 'active' : ''; ?>"><i class="fa-solid fa-certificate"></i><p>Jelvények</p></a></li>
                <li><a href="?page=panel_user_badges" class="<?php echo ($_GET['page'] ?? '') === 'panel_user_badges' ? 'active' : ''; ?>"><i class="fa-solid fa-award"></i><p>Felh. jelvények</p></a></li>
                <li><a href="?page=panel_images" class="<?php echo ($_GET['page'] ?? '') === 'panel_images' ? 'active' : ''; ?>"><i class="fa-solid fa-image"></i><p>Képek</p></a></li>
                <li><a href="?page=panel_cart" class="<?php echo ($_GET['page'] ?? '') === 'panel_cart' ? 'active' : ''; ?>"><i class="fa-solid fa-cart-shopping"></i><p>Kosár</p></a></li>
                <li><a href="?page=panel_groups" class="<?php echo ($_GET['page'] ?? '') === 'panel_groups' ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i><p>Csoportok</p></a></li>
                <li><a href="?page=panel_conversations" class="<?php echo ($_GET['page'] ?? '') === 'panel_conversations' ? 'active' : ''; ?>"><i class="fa-solid fa-comments"></i><p>Beszélgetések</p></a></li>
                <li><a href="?page=panel_messages" class="<?php echo ($_GET['page'] ?? '') === 'panel_messages' ? 'active' : ''; ?>"><i class="fa-solid fa-envelope"></i><p>Üzenetek</p></a></li>
                <li><a href="?page=panel_deals" class="<?php echo ($_GET['page'] ?? '') === 'panel_deals' ? 'active' : ''; ?>"><i class="fa-solid fa-tags"></i><p>Ajánlatok</p></a></li>
            </ul>
        </div>

        <!-- Jobb oldali tartalom -->
        <div class="main">
            <?php
                $page = $_GET['page'] ?? 'panel_dashboard'; // alapértelmezett oldal
                $allowed_pages = ['panel_dashboard', 'panel_users', 'panel_login', 'panel_products', 'panel_posts', 'panel_comments', 'panel_badges', 'panel_user_badges', 'panel_images', 'panel_cart', 'panel_groups', 'panel_conversations', 'panel_messages', 'panel_deals'];

                if (in_array($page, $allowed_pages)) {
                    $safe_filename = basename($page . '.php');
                    $filepath = __DIR__ . '/' . $safe_filename;
                    if (file_exists($filepath)) {
                        include $filepath;
                    } else {
                        echo "<h2>Oldalgenerálási hiba, próbáld újra!</h2>";
                    }
                } else {
                    echo "<h2>Oldalgenerálási hiba, próbáld újra!</h2>";
                }
            ?>
        </div>
    </div>
</body>
</html>
