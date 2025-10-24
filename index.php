<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <script src="index.js" defer></script>
    <title>Techoazis | Home</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<?php
$page = $_GET['p'] ?? '';  

if ($page === '') {
    include 'navbar.php';
?>

    <div class="hero-section">
        <div class="custom-container hero-container">
            <div class="hero-text">
                <h1>Csevegés, vásárlás, olvasás, meg persze a tech. Egy helyen.</h1>
                <p>Fedezze fel oldalunk nyújtotta szolgáltatásokat.</p>
                <a href="shop.php">
                    <button type="button" class="shopnow">Vásárolj most</button>
                </a>
            </div>
        </div>
    </div>

    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title">Mit találsz nálunk?</h2>
            <div class="grid-row">
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <i class="fa-solid fa-users fa-3x icon-primary"></i>
                        <h4>Közösség</h4>
                        <p>Csevegj, kérdezz, oszd meg tapasztalataid más techrajongókkal.</p>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <i class="fa-solid fa-cart-shopping fa-3x icon-success"></i>
                        <h4>Vásárlás</h4>
                        <p>Fedezd fel a legújabb technológiai termékeket a webshopunkban.</p>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <i class="fa-solid fa-book fa-3x icon-info"></i>
                        <h4>Tudástár</h4>
                        <p>Olvass cikkeket, útmutatókat és fejleszd a tudásod.</p>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <i class="fa-solid fa-diagram-project fa-3x icon-warning"></i>
                        <h4>Projektek</h4>
                        <p>Nézd meg, min dolgoznak mások vagy mutasd be a saját munkád.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gap"></div>

    <section class="section-padding">
        <div class="custom-container text-center">
            <h2 class="section-title">Friss tartalmak előnézete</h2>
            <div class="grid-row">
                <div class="grid-col-4">
                    <div class="custom-card reveal">
                        <img src="images/nikon_z50.jpg" class="card-img-top" alt="Cikk 1">
                        <div class="card-body">
                            <h5 class="card-title">Új projekt: SmartHub</h5>
                            <p class="card-text">Egy közösségi okos eszköz kezelő, amely forradalmasítja az IoT-t.</p>
                            <a href="#" class="shopnow-small">Tovább</a>
                        </div>
                    </div>
                </div>
                <div class="grid-col-4">
                    <div class="custom-card reveal">
                        <img src="images/ipad_air.jpg" class="card-img-top" alt="Cikk 2">
                        <div class="card-body">
                            <h5 class="card-title">Legújabb TechCikk</h5>
                            <p class="card-text">Miként változtatja meg az AI a mindennapi vásárlást?</p>
                            <a href="#" class="shopnow-small">Olvass tovább</a>
                        </div>
                    </div>
                </div>
                <div class="grid-col-4">
                    <div class="custom-card reveal">
                        <img src="images/macbook_air_m2.jpg" class="card-img-top" alt="Cikk 3">
                        <div class="card-body">
                            <h5 class="card-title">Új termék a shopban</h5>
                            <p class="card-text">Fedezd fel a legújabb tech kiegészítőket kedvező áron!</p>
                            <a href="#" class="shopnow-small">Vásárolj most</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gap"></div>

    <section class="custom-container section-padding">
        <div class="text-center">
            <h2 class="section-title">Miért válassz minket?</h2>
            <div class="grid-row">
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <h4>Gyors</h4>
                            <p>Villámgyors oldalbetöltés és optimalizált élmény minden eszközön.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-people-fill"></i>
                            <h4>Közösségi</h4>
                            <p>Beszélgess, ossz meg projekteket, és tanulj másoktól.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-cpu-fill"></i>
                            <h4>Modern</h4>
                            <p>A legfrissebb technológiákkal és eszközökkel építve.</p>
                        </div>
                    </div>
                </div>
                <div class="grid-col-3 reveal">
                    <div class="feature-card">
                        <div class="whyus-icon">
                            <i class="bi bi-shield-lock-fill"></i>
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
                    <h3 class="footer-title">Techoázis</h3>
                    <p class="footer-description">
                        A hely, ahol a technológia, a közösség és az innováció találkozik.
                    </p>
                </div>
                <div class="grid-col-4 footer-nav">
                    <h3 class="footer-subtitle">Navigáció</h3>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">Főoldal</a></li>
                        <li><a href="shop.php" class="footer-link">Webshop</a></li>
                        <li><a href="forum.php" class="footer-link">Csevegés</a></li>
                        <li><a href="articles.php" class="footer-link">Cikkek</a></li>
                        <li><a href="contact.php" class="footer-link">Kapcsolat</a></li>
                        <?php
                        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                            <li><a href="admin_panel.php" class="footer-link">Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="grid-col-4 footer-social">
                    <h3 class="footer-subtitle">Kövess minket</h3>
                    <div class="social-icons-wrapper">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
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
    // Dinamikusan betöltjük az adott oldal fájlját, ha létezik
    $file = $page . '.php';
    
    if (file_exists($file)) {
        include $file;
    } else {
        include '404.php';
    }
}
?>

</body>
</html>