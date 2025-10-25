<?php require 'auth_check.php'; // Oldal védelme 
$page = $_GET['p'] ?? '';  

if ($page === '') {
    include 'navbar.php';
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <script src="index.js" defer></script>
    <title>Techoazis | Adminpanel</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <!-- Hero szekció -->
    <div class="hero-section">
        <div class="custom-container hero-container">
            <div class="hero-text">
                <h1>Üdv az Admin Panelben, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p>Itt kezelheted a felhasználókat, termékeket és bejegyzéseket.</p>
            </div>
        </div>
    </div>
    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title">Mit tudsz csinálni?</h2>
            <div class="grid-row">
                <div class="grid-col-3 reveal">
                    <a href="users.php" class="btn">
                    <div class="feature-card">
                        <i class="fa-solid fa-user-gear fa-3x icon-primary"></i>
                        <h4>Felhasználók</h4>
                        <p>Felhasználói fiókok kezelése, jogosultságok módosítása.</p>
                        <span class="btn"></span>
                    </div>
                    </a>
                </div>
                <div class="grid-col-3 reveal">
                    <a href="products.php" class="btn">
                    <div class="feature-card">
                        <i class="fa-solid fa-box-open fa-3x icon-success"></i>
                        <h4>Termékek</h4>
                        <p>Termékek hozzáadása, szerkesztése és törlése a webshopban.</p>
                    </div>
                    </a>
                </div>
                <div class="grid-col-3 reveal">
                    <a href="posts.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-pen-to-square fa-3x icon-info"></i>
                            <h4>Bejegyzések</h4>
                            <p>Blogbejegyzések és cikkek kezelése, publikálása.</p>
                        </div>
                    </a>
                </div>
                <div class="grid-col-3 reveal">
                    <a href="easter_egg.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-mug-hot fa-3x icon-warning"></i>
                            <h4>Stresszoldó mód</h4>
                            <p>Ha túl sok a bug, túl kevés a kávé, itt egy kis nyugi.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>