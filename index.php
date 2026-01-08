<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | A közösség és a technológia egy helyen</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/comments.css">
    <link rel="stylesheet" href="./static/create_post.css">
    <link rel="stylesheet" href="./static/forum.css">
    <link rel="stylesheet" href="./static/group_view.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/post_card.css">
    <link rel="stylesheet" href="./static/responsive_adjustments.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="./static/custom_card.css">
    <link rel="stylesheet" href="./static/feature_cards.css">
    <link rel="stylesheet" href="./static/hero_section.css">
    <link rel="stylesheet" href="./static/loading_animation.css">
    <link rel="stylesheet" href="./static/modern_footer.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/utility_classes.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/index.js" defer></script>
    <script src="./static/forum.js" defer></script>

</head>
<body>
<?php
$page = $_GET['p'] ?? '';

// Whitelist of allowed pages to prevent LFI attacks
$allowed_pages = ['shop', 'forum', 'forum_group', 'cart', 'profile', 'profile_edit', 'create_post'];

if ($page === '') {
    include 'views/navbar.php';
?>

    <!-- Loading screen hozzáadása -->
    <!--<div id="loader">
        <div class="loader-logo">
            <span class="gradient-text">Techo</span><span class="text-accent">ázis</span>
        </div>
        <div class="spinner"></div>
    </div>-->

    <div class="hero-section">
        <div class="custom-container hero-container">
            <div class="hero-text">
                <h1>Csevegés, vásárlás, olvasás, meg persze a tech. Egy helyen.</h1>
                <p>Fedezze fel oldalunk nyújtotta szolgáltatásokat.</p>
                <a href="shop.php">
                    <button type="button" class="btn btn-primary shopnow">Vásárolj most ➔</button>
                </a>
            </div>
        </div>
    </div>
    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title">Mit találsz nálunk?</h2>
            <div class="grid-row">
                <div class="grid-col-3">
                    <a href="./forum.php" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-users fa-3x icon-primary"></i>
                            <h4>Közösség</h4>
                            <p>Csevegj, kérdezz, oszd meg tapasztalataid más techrajongókkal.</p>
                        </div>
                    </a>
                </div>
                <div class="grid-col-3">
                    <a href="./shop.php" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-cart-shopping fa-3x icon-success"></i>
                            <h4>Vásárlás</h4>
                            <p>Fedezd fel a legújabb technológiai termékeket a webshopunkban.</p>
                        </div>
                    </a>
                </div>
                <div class="grid-col-3">
                    <a href="#" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-book fa-3x icon-info"></i>
                            <h4>Tudástár</h4>
                            <p>Olvass cikkeket, útmutatókat és fejleszd a tudásod.</p>
                        </div>
                    </a>
                </div>
                <div class="grid-col-3">
                    <a href="#" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-diagram-project fa-3x icon-warning"></i>
                            <h4>Projektek</h4>
                            <p>Nézd meg, min dolgoznak mások vagy mutasd be a saját munkád.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="gap"></div>

    <section class="section-padding">
        <div class="custom-container text-center">
            <h2 class="section-title reveal">Friss tartalmak előnézete</h2>
            <div class="grid-row">
                <div class="grid-col-4 reveal">
                    <div class="custom-card">
                        <img src="images/nikon_z50.jpg" class="card-img-top" alt="Cikk 1">
                        <div class="card-body">
                            <h5 class="card-title">Új projekt: SmartHub</h5>
                            <p class="card-text">Egy közösségi okos eszköz kezelő, amely forradalmasítja az IoT-t.</p>
                            <div class="card-footer">
                                <a href="#" class="btn btn-secondary shopnow-small">Tovább</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid-col-4 reveal">
                    <div class="custom-card">
                        <img src="images/ipad_air.jpg" class="card-img-top" alt="Cikk 2">
                        <div class="card-body">
                            <h5 class="card-title">Legújabb TechCikk</h5>
                            <p class="card-text">Miként változtatja meg az AI a mindennapi vásárlást?</p>
                            <div class="card-footer">
                                <a href="#" class="btn btn-secondary shopnow-small">Olvass tovább</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid-col-4 reveal">
                    <div class="custom-card">
                        <img src="images/macbook_air_m2.jpg" class="card-img-top" alt="Cikk 3">
                        <div class="card-body">
                            <h5 class="card-title">Új termék a shopban</h5>
                            <p class="card-text">Fedezd fel a legújabb tech kiegészítőket kedvező áron!</p>
                            <div class="card-footer">
                                <a href="#" class="btn btn-primary shopnow-small">Vásárolj most</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gap"></div>

    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title reveal">Miért válassz minket?</h2>
            <div class="grid-row">
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-lightning-charge-fill fa-3x"></i>
                            <h4>Gyors</h4>
                            <p>Villámgyors oldalbetöltés és optimalizált élmény minden eszközön.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-people-fill fa-3x"></i>
                            <h4>Közösségi</h4>
                            <p>Beszélgess, ossz meg projekteket, és tanulj másoktól.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-cpu-fill fa-3x"></i>
                            <h4>Modern</h4>
                            <p>A legfrissebb technológiákkal és eszközökkel építve.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-shield-lock-fill fa-3x"></i>
                            <h4>Biztonságos</h4>
                            <p>Adatvédelem és biztonság a legmagasabb szinten.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gap"></div>

    <footer class="footer">
        <div class="custom-container">
            <div class="grid-row">
                <div class="grid-col-4">
                    <div class="footer-brand">
                        <h3 class="footer-subtitle">Techoázis</h3>
                        <p class="footer-description">
                            A hely, ahol a technológia, a közösség és az innováció találkozik.
                        </p>
                    </div>
                </div>
                <div class="grid-col-4 footer-nav">
                    <h3 class="footer-title">Navigáció</h3>
                    <ul class="footer-links">
                        <li><a href="./index.php" class="footer-link"><i class="fas fa-home"></i> Főoldal</a></li>
                        <li><a href="./shop.php" class="footer-link"><i class="fas fa-shopping-cart"></i> Webshop</a></li>
                        <li><a href="./forum.php" class="footer-link"><i class="fas fa-comments"></i> Csevegés</a></li>
                        <!-- <li><a href="./articles.php" class="footer-link">Cikkek</a></li> -->
                        <!-- <li><a href="./contact.php" class="footer-link">Kapcsolat</a></li> -->
                        <?php
                        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                            <li><a href="admin/admin.php" class="footer-link"><i class="fas fa-cog"></i> Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="grid-col-4 footer-social">
                    <h3 class="footer-title">Kövess minket</h3>
                    <div class="social-icons-wrapper">
                        <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon" aria-label="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
                        <a href="#" class="social-icon" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Techoázis. Minden jog fenntartva.
            </div>
        </div>
    </footer>
<?php
} else {
    // Dinamikusan betöltjük az adott oldal fájlját, ha létezik és engedélyezett
    if (in_array($page, $allowed_pages)) {
        $file = $page . '.php';

        if (file_exists($file)) {
            include $file;
        } else {
            include 'views/404.php';
        }
    } else {
        include 'views/404.php';
    }
}
?>

</body>
</html>