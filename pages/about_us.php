<?php
session_start();

require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | Rólunk</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/about_us_style.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>


</head>
<body>

<?php include ROOT_PATH . '/views/navbar.php'; ?>
    <!-- HERO -->
    <div class="hero-section">
        <div class="custom-container hero-container">
            <div class="hero-text">
                <h1>Rólunk</h1>
                <p>A Techoázis egy vizsgaremek projektből indult, de a célunk az első pillanattól az volt,
                    hogy egy valóban használható, közösségvezérelt technológiai platformot építsünk.
                </p>
            </div>
        </div>
    </div>

    <!-- 1. FEHÉR: Sztori -->
    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title">A történetünk</h2>
            <p class="lead">
                A Techoázis a technológia és az “oázis” gondolatának találkozásából született:
                egy üde, átlátható pont a digitális zaj közepén.
            </p>
        </div>
        <div class="two-col">
            <div>
                <p>
                    A projekt egy vizsgaremek keretein belül indult, ahol a cél egy modern technológiai platform létrehozása volt.
                    Olyan felületet akartunk építeni, ami nem csak “megvan”, hanem ténylegesen jól használható, egységes és bővíthető.
                </p>
                <p>
                    A név ötlete is innen jött: ahogy egy oázis a sivatagban egy stabil és friss pont, úgy szeretnénk mi is egy olyan hely lenni,
                    ahová vissza lehet térni technológiai hírekért, véleményekért, és olyan tartalmakért, amik tényleg érdeklik az embert.
                </p>
            </div>

            <div class="callout">
                <h3>Miért “oázis”?</h3>
                <p>
                    Mert célunk egy tiszta, átlátható és közösségi tér létrehozása,
                    ahol a technológia nem csak fogyasztott tartalom, hanem beszélgetés és együtt töltött idő is.
                </p>
            </div>
        </div>
    </section>

    <!-- 2. HALVÁNYKÉK: Küldetés + “miért egy helyen?” -->
    <section class="section-padding diff">
        <div class="container">
            <div class="section-head">
                <h2>Küldetésünk</h2>
                <p class="lead">
                    Egy olyan közösségi platform építése, ahol a tájékozódás,
                    a véleménycsere és a piactér egyetlen rendszerben találkozik.
                </p>
            </div>

            <div class="two-col">

                <!-- BAL: SZÖVEG PANEL -->
                <div class="content-panel">
                    <p>
                        Ma a felhasználók gyakran kénytelenek több különböző oldalt is bejárni:
                        híroldalak, fórumok, közösségi felületek és piacterek között ugrálnak.
                    </p>

                    <p>
                        A Techoázis ötlete ebből a problémából indult:
                        mi lenne, ha mindez egy helyen lenne, egységes felülettel,
                        átlátható struktúrával.
                    </p>

                    <p>
                        Ezért egyesítjük a friss technológiai híreket,
                        az aktív fóruméletet plusz egy tematikus marketplace-et.
                    </p>
                </div>

                <!-- JOBB: FEATURE ELEMEK -->
                <div class="content-panel feature-cards">
                    <div class="mini-card">
                        <i class="fa-solid fa-newspaper"></i>
                        <div>
                            <h4>Hírek és cikkek</h4>
                            <p>Fejlesztők által készített tartalom.</p>
                        </div>
                    </div>

                    <div class="mini-card">
                        <i class="fa-solid fa-comments"></i>
                        <div>
                            <h4>Közösségi fórum</h4>
                            <p>Tematikus beszélgetések, aktív részvétel.</p>
                        </div>
                    </div>

                    <div class="mini-card">
                        <i class="fa-solid fa-store"></i>
                        <div>
                            <h4>Marketplace</h4>
                            <p>Technológiai termékek egy helyen.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 5. FEHÉR: Jelen + irány -->
    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title">Hol tartunk most?</h2>
            <p class="lead">
                Friss platform vagyunk — ezért a legfontosabb számunkra a stabil alap és a fejlődés, emellett a felhasználóinkra is figyelmet fordítunk.
            </p>
        </div>
        <div class="two-col">
            <div>
                <p>
                    A Techoázis jelenleg egy olyan fázisban van, ahol az alapfunkciók már együtt működnek,
                    és a visszajelzések alapján finomhangoljuk a felhasználói élményt.
                    A célunk nem üres ígéreteket tenni: inkább stabilan építkezünk.
                </p>
                <p>
                    A következő nagy lépések általában akkor születnek meg, ahol a közösség már használni kezdi a rendszert:
                    mi működik, mi hiányzik, mit lehet egyszerűsíteni, mit érdemes erősíteni.
                </p>
            </div>

            <div class="callout">
                <h3>Minőségi alap</h3>
                <p>
                    A vizsgaremeken túl is fontos, hogy a platform átlátható, karbantartható és bővíthető maradjon -
                    ezért a fejlesztés során végig erre építünk.
                </p>
            </div>
        </div>
    </section>


    <!-- 4. HALVÁNYKÉK: Csapat -->
    <section class="section-padding diff">
        <div class="container">
            <div class="section-head">
                <h2>Csapattagok</h2>
                <p class="lead">
                    A Techoázist két partnerfejlesztő hozta létre, akik a platformot tulajdonosként is gondozzák.
                </p>
            </div>

            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <h3>Berta György</h3>
                    <p class="role">Partnerfejlesztő - Társtulajdonos</p>
                    <p>
                        A cél egy olyan közösségi felület kialakítása, ami vizuálisan is egységes, és folyamatosan bővíthető.
                    </p>
                </div>

                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <h3>Pap Máté</h3>
                    <p class="role">Partnerfejlesztő - Társtulajdonos</p>
                    <p>
                        A platform fejlesztésében a rendszerlogika, funkcionalitás kiemelt fókuszt érdemel.
                    </p>
                </div>
            </div>
        </div>
    </section>

    
    <!-- 6. HALVÁNYKÉK: Hamarosan (kártyák csak itt) -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Hamarosan</h2>
                <p class="lead">
                    Ezek a bővítések már tervben vannak - a platform jövőjét a közösség és a folyamatos fejlesztés együtt formálja.
                </p>
            </div>

            <div class="future-grid">
                <div class="future-card">
                    <i class="fa-solid fa-bullhorn"></i>
                    <h3>Promóciós lehetőségek</h3>
                    <p>Később célzott megjelenés és kiemelések a platformon belül.</p>
                    <span class="soon-badge">Hamarosan</span>
                </div>

                <div class="future-card">
                    <i class="fa-solid fa-cloud-sun"></i>
                    <h3>Külső API-k</h3>
                    <p>Pl. időjárás, integrációk, hasznos modulok a felhasználóknak.</p>
                    <span class="soon-badge">Hamarosan</span>
                </div>

                <div class="future-card">
                    <i class="fa-solid fa-mobile-screen"></i>
                    <h3>Mobilapp</h3>
                    <p>Kényelmesebb hozzáférés, gyorsabb interakciók, értesítések.</p>
                    <span class="soon-badge">Később</span>
                </div>

                <div class="future-card">
                    <i class="fa-solid fa-crown"></i>
                    <h3>Prémium funkciók</h3>
                    <p>Extra testreszabás, bővített lehetőségek aktív felhasználóknak.</p>
                    <span class="soon-badge">Később</span>
                </div>

                <div class="future-card">
                    <i class="fa-solid fa-code"></i>
                    <h3>Saját kódfuttató</h3>
                    <p>Fejlesztői eszközök és interaktív megoldások a platformon belül.</p>
                    <span class="soon-badge">Később</span>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="custom-container">
            <!-- FELSŐ RÉSZ -->
            <div class="footer-top">
                <!-- BRAND -->
                <div class="footer-brand">
                    <h3 class="footer-subtitle">Techoázis</h3>
                    <p class="footer-description">
                        A hely, ahol a technológia, a közösség és az innováció találkozik.
                    </p>
                </div>
                <!-- JOBB OLDALI BLOKK -->
                <div class="footer-right">
                    <!-- NAV -->
                    <div class="footer-section">
                        <h3 class="footer-title">Navigáció</h3>
                        <ul class="footer-links">
                            <li><a href="<?= BASE_URL ?>/index.php" class="footer-link">Főoldal</a></li>
                            <li><a href="<?= BASE_URL ?>/shop.php" class="footer-link">Vásárlás</a></li>
                            <li><a href="<?= BASE_URL ?>/forum.php" class="footer-link">Közösség</a></li>
                            <li><a href="<?= BASE_URL ?>/articles.php" class="footer-link">Tudástár</a></li>
                            <li><a href="<?= BASE_URL ?>/about_us.php" class="footer-link">Rólunk</a></li>
                        </ul>
                    </div>
                </div>
                <div class="grid-col-4 footer-nav">
                    <h3 class="footer-title">Navigáció</h3>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/index.php" class="footer-link"><i class="fas fa-home"></i> Főoldal</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/shop.php" class="footer-link"><i class="fas fa-shopping-cart"></i> Webshop</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/forum.php" class="footer-link"><i class="fas fa-comments"></i> Csevegés</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/articles.php" class="footer-link"><i class="fa-solid fa-pen"></i> Csevegés</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/about_us.php" class="footer-link"><i class="fa-solid fa-address-card"></i> Rólunk</a></li>
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
            <!-- ALSÓ RÉSZ -->
            <div class="footer-bottom">
                <div class="social-icons-wrapper">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>

                <div class="footer-copy">
                    &copy; <?php echo date('Y'); ?> Techoázis. Minden jog fenntartva.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
