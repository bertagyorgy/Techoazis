<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/core/config.php';

// Tisztítás: levágjuk a perjeleket az elejéről és végéről
$page = isset($_GET['p']) ? trim($_GET['p'], '/') : '';

$allowed_pages = [
    'shop', 'forum', 'forum_group', 'contact', 'cart', 'profile', 
    'product_detail', 'profile_edit', 'create_post', 'articles',    
    'conversation', 'article_detail', 'about_us', 
    'login', 'registration', 'logout', 'forgot_password'
];

// --- SZIGORÚ ROUTING ---
if ($page !== '' && $page !== 'index') {
    // Ha a kérésben van "/" (pl. asd/asd), vagy nincs a listában, akkor az 404
    // Kivéve, ha később akarsz aloldalakat, de most a biztonság a cél
    if (in_array($page, $allowed_pages)) {
        $views_file = ROOT_PATH . '/views/' . $page . '.php';
        $root_file = ROOT_PATH . '/' . $page . '.php';

        if (file_exists($views_file)) {
            include $views_file;
            exit; 
        } elseif (file_exists($root_file)) {
            include $root_file;
            exit;
        }
    }
    
    // Ha ide eljut (pl. asd/asd vagy ismeretlen oldal), tiszta 404-et dobunk
    http_response_code(404);
    if (file_exists(ROOT_PATH . '/views/404.php')) {
        include ROOT_PATH . '/views/404.php';
    } else {
        echo "<h1>404 - Az oldal nem található</h1>";
    }
    exit; // FONTOS: Megállítjuk a futást, nem töltünk be semmi mást!
}
?>



<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Techoázis: hardver piactér, tech közösség, cikkek és biztonságos adás-vétel. Vásárolj, adj el és beszélgess biztonságosan.">
    <base href="<?= BASE_URL ?>/">
    <title>Techoázis - A közösség és a technológia egy helyen</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/create_post.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/group_view.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/post_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/feature_cards.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/hero_section.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/contact_style.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>

</head>
<body>
    <?php include ROOT_PATH . '/views/navbar.php';?>

    <div class="hero-section">
        <div class="custom-container hero-container">
            <div class="hero-text">
                <h1>Csevegés, vásárlás, olvasás, meg persze a tech. Egy helyen.</h1>
                <p>Fedezze fel oldalunk nyújtotta szolgáltatásokat.</p>
                <a href="<?= BASE_URL ?>/pages/shop.php">
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
                    <a href="<?= BASE_URL ?>/pages/articles.php" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-book fa-3x icon-info"></i>
                            <h4>Tudástár</h4>
                            <p>Olvass cikkeket, útmutatókat és fejleszd a tudásod.</p>
                        </div>
                    </a>
                </div>
                <div class="grid-col-3">
                    <a href="<?= BASE_URL ?>/pages/forum.php" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-users fa-3x icon-primary"></i>
                            <h4>Közösség</h4>
                            <p>Csevegj, kérdezz, oszd meg tapasztalataid más techrajongókkal.</p>
                        </div>
                    </a>
                </div>
                <div class="grid-col-3">
                    <a href="<?= BASE_URL ?>/pages/shop.php" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-cart-shopping fa-3x icon-success"></i>
                            <h4>Vásárlás</h4>
                            <p>Fedezd fel felhasználóink által hirdetett legújabb technológiai termékeket.</p>
                        </div>
                    </a>
                </div>
                <div class="grid-col-3">
                    <a href="<?= BASE_URL ?>/pages/profile.php" class="feature-card-link">
                        <div class="feature-card">
                            <i class="fa-solid fa-store"></i>
                            <h4>Eladás</h4>
                            <p>Kereskedj, add el a nem használt kütyüidet és keress pénzt.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding diff">
        <div class="custom-container text-center">
            <h2 class="section-title reveal">Friss tartalmak előnézete</h2>
            <div class="grid-row">
                <div class="grid-col-4 reveal">
                    <div class="custom-card">
                        <img src="<?= BASE_URL ?>/images/ipad_air.jpg" class="card-img-top" alt="Cikk 1">
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
                        <img src="<?= BASE_URL ?>/images/macbook_air_m2.jpg" class="card-img-top" alt="Termék 2">
                        <div class="card-body">
                            <h5 class="card-title">Új termék a shopban</h5>
                            <p class="card-text">Fedezd fel a legújabb tech kiegészítőket kedvező áron!</p>
                            <div class="card-footer">
                                <a href="#" class="btn btn-primary shopnow-small">Vásárolj most</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid-col-4 reveal">
                    <div class="custom-card">
                        <img src="<?= BASE_URL ?>/uploads/posts/4_1763756851_4212.jpg" class="card-img-top" alt="Poszt 3">
                        <div class="card-body">
                            <h5 class="card-title">Legújabb poszt</h5>
                            <p class="card-text">Milyen hasznuk van a keretrendszereknek?</p>
                            <div class="card-footer">
                                <a href="#" class="btn btn-secondary shopnow-small">Tovább</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title reveal">A Techoázis számokban</h2>
            <div class="grid-row">
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-user"></i>
                            <h3>100+</h3>
                            <p>Felhasználó</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-pen-nib"></i>
                            <h3>50+</h3>
                            <p>Poszt és cikk</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-layer-group"></i>
                            <h3>20+</h3>
                            <p>Témakör</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-tag"></i>
                            <h3>10+</h3>
                            <p>Feltöltött termék</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding diff">
        <div class="custom-container text-center">
            <h2 class="section-title reveal">Gyakran Ismételt Kérdések</h2>
            <section class="faq-card">
                <div class="faq">
                    <details class="faq-item">
                        <summary>Hogyan működik a biztonságos adás-vétel?</summary>
                        <p>Mindkét félnek jóvá kell hagynia az üzletet, majd a beszélgetés lezárható. Lezárás után értékelheted az eladót.</p>
                    </details>

                    <details class="faq-item">
                        <summary>Miért nem tudok üzenetet küldeni?</summary>
                        <p>Ellenőrizd, hogy be vagy-e jelentkezve és a beszélgetés nincs-e lezárva.</p>
                    </details>

                    <details class="faq-item">
                        <summary>Hol látom az értékeléseket?</summary>
                        <p>Az értékelések az eladó profilján jelennek meg az utolsó értékelések szekcióban.</p>
                    </details>

                    <details class="faq-item">
                        <summary>Mennyi idő alatt válaszoltok?</summary>
                        <p>Általában 24-48 órán belül.</p>
                    </details>
                </div>
            </section>
        </div>
    </section>

    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title reveal">Miért válassz minket?</h2>
            <div class="grid-row">
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-bolt"></i>
                            <h4>Gyors</h4>
                            <p>Villámgyors oldalbetöltés és optimalizált élmény minden eszközön.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-users"></i>
                            <h4>Közösségi</h4>
                            <p>Beszélgess, ossz meg projekteket, és tanulj másoktól.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                            <h4>Ingyenes</h4>
                            <p>Felhasználóink díjmentesen adhatják el termékeiket az oldalon.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="fa-solid fa-lock"></i>
                            <h4>Biztonságos</h4>
                            <p>Adatvédelem és biztonság a legmagasabb szinten.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a href="<?= BASE_URL ?>/index.php" class="footer-link"><i class="fas fa-home"></i> Főoldal</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/shop.php" class="footer-link"><i class="fas fa-shopping-cart"></i> Webshop</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/forum.php" class="footer-link"><i class="fas fa-comments"></i> Csevegés</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/articles.php" class="footer-link"><i class="fa-solid fa-pen"></i> Cikkek</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/contact.php" class="footer-link"><i class="fa-solid fa-address-card"></i> Kapcsolat</a></li>
                        <?php
                        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                            <li><a href="<?= BASE_URL ?>/admin/admin.php" class="footer-link"><i class="fas fa-cog"></i> Admin</a></li>
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
                    

</body>
</html>