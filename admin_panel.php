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
                <!-- Felhasználók -->
                <div class="grid-col-3 reveal">
                    <a href="panel_users.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-user-gear fa-3x icon-primary"></i>
                            <h4>Felhasználók</h4>
                            <p>Felhasználói fiókok kezelése, jogosultságok módosítása.</p>
                        </div>
                    </a>
                </div>
                <!-- Bejelentkezések -->
                <div class="grid-col-3 reveal">
                    <a href="panel_login.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-right-to-bracket fa-3x icon-success"></i>
                            <h4>Bejelentkezések</h4>
                            <p>Felhasználói aktivitás és bejelentkezési előzmények megtekintése.</p>
                        </div>
                    </a>
                </div>
                <!-- Termékek -->
                <div class="grid-col-3 reveal">
                    <a href="panel_products.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-box-open fa-3x icon-info"></i>
                            <h4>Termékek</h4>
                            <p>Termékek hozzáadása, szerkesztése és törlése a webshopban.</p>
                        </div>
                    </a>
                </div>
                <!-- Bejegyzések -->
                <div class="grid-col-3 reveal">
                    <a href="panel_posts.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-pen-to-square fa-3x icon-warning"></i>
                            <h4>Bejegyzések</h4>
                            <p>Blogbejegyzések és cikkek kezelése, publikálása.</p>
                        </div>
                    </a>
                </div>
                <!-- Kommentek -->
                <div class="grid-col-3 reveal">
                    <a href="panel_comments.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-comments fa-3x icon-primary"></i>
                            <h4>Kommentek</h4>
                            <p>Hozzászólások moderálása, törlése és válaszok kezelése.</p>
                        </div>
                    </a>
                </div>
                <!-- Jelvények -->
                <div class="grid-col-3 reveal">
                    <a href="panel_badges.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-medal fa-3x icon-success"></i>
                            <h4>Jelvények</h4>
                            <p>Új jelvények létrehozása, ikonok és leírások kezelése.</p>
                        </div>
                    </a>
                </div>
                <!-- Felhasználói jelvények -->
                <div class="grid-col-3 reveal">
                    <a href="panel_user_badges.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-star fa-3x icon-info"></i>
                            <h4>Felhasználói jelvények</h4>
                            <p>Megtekintheted, hogy melyik felhasználó milyen jelvényeket szerzett meg és mikor.</p>
                        </div>
                    </a>
                </div>
                <!-- Képek -->
                <div class="grid-col-3 reveal">
                    <a href="panel_images.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-image fa-3x icon-warning"></i>
                            <h4>Képek</h4>
                            <p>Termékekhez és bejegyzésekhez tartozó képek feltöltése és kezelése.</p>
                        </div>
                    </a>
                </div>
                <!-- Kosár -->
                <div class="grid-col-3 reveal">
                    <a href="panel_cart.php" class="btn">
                        <div class="feature-card">
                            <i class="fa-solid fa-cart-shopping fa-3x icon-primary"></i>
                            <h4>Kosár</h4>
                            <p>Felhasználói kosarak megtekintése, termékek mennyiségének módosítása.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

</body>
</html>