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
    <meta name="description" content="Techoázis: hardver piactér, tech közösség, cikkek és biztonságos adás-vétel. Vásárolj, adj el és beszélgess biztonságosan egy helyen.">
    <base href="<?= BASE_URL ?>/">
    <title>Techoázis - A közösség és a technológia egy helyen</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main-index.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>

</head>
<body>
    <?php include ROOT_PATH . '/views/navbar.php';?>

    <main class="main-page-wrapper">
        <section class="main-hero">
            <div class="main-container">
                <div class="main-hero-content">
                    <span class="main-badge">Üdvözöl az Oázis 2.0</span>
                    <h1 class="main-hero-title">A technológia, <br><span class="main-text-gradient">ahogy még nem láttad.</span></h1>
                    
                    <div class="main-search-container">
                        <form action="search.php" method="GET" class="main-search-wrapper">
                            <div class="main-search-type">
                                <select name="type">
                                    <option value="market">Piactér</option>
                                    <option value="articles">Tudástár</option>
                                    <option value="forum">Közösség</option>
                                </select>
                            </div>
                            <input type="text" name="query" placeholder="RTX 4090, AI hírek, segítség...">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>Mehet</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <div class="main-trust-bar">
            <div class="main-container">
                <div class="main-trust-grid">
                    <div class="main-trust-item">
                        <i class="fa-solid fa-user-group"></i> 
                        <div><strong>100+</strong> <span>Aktív tag</span></div>
                    </div>
                    <div class="main-trust-item">
                        <i class="fa-solid fa-shield-halved"></i> 
                        <div><strong>Biztonság</strong> <span>Moderált piac</span></div>
                    </div>
                    <div class="main-trust-item">
                        <i class="fa-solid fa-bolt"></i> 
                        <div><strong>Gyorsaság</strong> <span>Azonnali válasz</span></div>
                    </div>
                    <div class="main-trust-item">
                        <i class="fa-solid fa-hand-holding-dollar"></i> 
                        <div><strong>Ingyenes</strong> <span>0% jutalék</span></div>
                    </div>
                </div>
            </div>
        </div>

        <section class="main-section">
            <div class="main-container">
                <div class="main-header-flex">
                    <div class="main-title-group">
                        <h2 class="main-section-title">Friss az Oázisban</h2>
                        <p class="main-section-subtitle">A legújabb tech kincsek és hírek egy helyen</p>
                    </div>
                    <a href="shop" class="main-view-all-btn">Összes böngészése <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                
                <div class="main-modern-grid">
                    <div class="main-grid-item main-featured">
                        <div class="main-card-overlay">
                            <span class="main-item-tag">Legújabb termék</span>
                            <h3>Piactér ajánlatai</h3>
                        </div>
                    </div>
                    <div class="main-grid-item main-secondary-card">
                        <div class="main-card-overlay">
                            <span class="main-item-tag">Tudástár</span>
                            <h3>Tech hírek</h3>
                        </div>
                    </div>
                    <div class="main-grid-item main-secondary-card">
                        <div class="main-card-overlay">
                            <span class="main-item-tag">Közösség</span>
                            <h3>Aktív fórum</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="main-section main-bg-alt">
            <div class="main-container">
                <h2 class="text-center" style="margin-bottom: 3rem;">Gyakran Ismételt Kérdések</h2>
                <div class="main-faq-list">
                    <details class="main-faq-item">
                        <summary>Hogyan működik a biztonságos adás-vétel?</summary>
                        <p>Mindkét félnek jóvá kell hagynia az üzletet, majd a beszélgetés lezárható. <br>
                        Lezárás után értékelheted az eladót.
                        </p>
                    </details>

                    <details class="main-faq-item">
                        <summary>Miért nem tudok üzenetet küldeni?</summary>
                        <p>Ellenőrizd, hogy be vagy-e jelentkezve és a beszélgetés nincs-e lezárva.</p>
                    </details>

                    <details class="main-faq-item">
                        <summary>Hol látom az értékeléseket?</summary>
                        <p>
                            Az értékelések az eladó profilján jelennek meg az legutóbbi értékelések szekcióban.<br>
                            Ezeket az értékeléseket korábbi üzletfeleid teszik közzé és a saját profiloldalon is látszanak.
                        </p>
                    </details>

                    <details class="main-faq-item">
                        <summary>Mennyi idő alatt válaszoltok?</summary>
                        <p>Általában 24-48 órán belül.</p>
                    </details>

                    <details class="main-faq-item">
                        <summary>Hogyan tudok terméket eladni a Techoázison?</summary>
                        <p>
                            Az eladás néhány egyszerű lépésből áll:
                            <br><br>
                            • Jelentkezz be a fiókodba.<br>
                            • Kattints a „Új termék feladása” gombra.<br>
                            • Add meg a termék nevét, leírását és az árát.<br>
                            • Tölts fel legalább egy képet a termékről.<br>
                            • Mentsd el a hirdetést.<br><br>
                            Ezután a termék megjelenik a piactéren, és más felhasználók üzenetet küldhetnek az ajánlatról.
                        </p>
                    </details>

                    <details class="main-faq-item">
                        <summary>Mi történik, ha véletlenül töröltem a fiókomat?</summary>
                        <p>
                            Ha törölted a fiókodat, az adatok általában véglegesen eltávolításra kerülnek a rendszerből.
                            Ilyenkor új fiókot kell létrehoznod egy másik e-mail címmel.
                            Ha úgy gondolod, hogy a törlés véletlen volt, érdemes minél hamarabb felvenni a kapcsolatot az adminisztrátorral fiókod visszaállítása érdekében.
                        </p>
                    </details>

                    <details class="main-faq-item">
                        <summary>Hogyan tudom szerkeszteni vagy törölni a feltöltött termékemet?</summary>
                        <p>
                            A feltöltött hirdetéseidet a vásárlásnál a saját termékednél alul a „Termék szerkesztése” menüpont alatt találod.
                            Itt lehetőséged van:
                            <br><br>
                            • módosítani a termék leírását vagy árát<br>
                            • új képet feltölteni<br>
                            • vagy teljesen törölni/rejtetté tenni a hirdetést
                        </p>
                    </details>
                </div>
            </div>
        </section>
    </main>

    <?php include ROOT_PATH . '/views/footer.php';?>
                    

</body>
</html>