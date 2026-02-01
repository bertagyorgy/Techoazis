<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//require_once 'config.php';

include_once ROOT_PATH . '/app/db.php';

?>
<script src="/static/index.js" defer></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<nav class="main-navbar">
    <div class="custom-container nav-container">
        <a class="nav-brand" href="<?= BASE_URL ?>"> 
            <img src="<?= BASE_URL  ?>images/techoazis_logo_chopped.png" alt="kep">
        </a>
        
        <button class="nav-toggler" id="navToggle" aria-label="Menü">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <div class="nav-collapse" id="navCollapseContent">
            <ul class="nav-menu">
                <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>">Főoldal</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>articles.php">Tudástár</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>forum.php">Közösség</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>shop.php">Vásárlás</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>contact.php">Kapcsolat</a></li>
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/admin.php" >Admin</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-icons mobile-icons">
                <a href='<?= BASE_URL ?>shop.php' class='icon-button' title='Keresés'><i class='fa-solid fa-magnifying-glass'></i></a>
                <!--<a href='<= BASE_URL ?>cart.php' class='icon-button cart-icon' title='Kosár'>
                    <i class='fa-solid fa-cart-shopping'></i>
                    <span class='cart-badge'><php echo $cart_badge; ?></span>
                </a>-->
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <a href='<?= BASE_URL ?>profile.php' class='icon-button' title='Profil'><i class='fa-solid fa-user'></i></a>
                    <a href="<?= BASE_URL ?>views/logout.php" class="icon-button" title="Kijelentkezés"> 
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>views/login.php" class="icon-button" title='Bejelentkezés'><i class="fa-solid fa-user"></i></a>
                    <a href='<?= BASE_URL ?>views/registration.php' class='icon-button-re' title='Regisztráció'>Regisztráció</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="nav-icons desktop-icons">
            <a href='<?= BASE_URL ?>shop.php' class='icon-button' title='Keresés'><i class='fa-solid fa-magnifying-glass'></i></a>
            <!--<a href='<?= BASE_URL ?>cart.php' class='icon-button cart-icon' title='Kosár'>
                <i class='fa-solid fa-cart-shopping'></i>
                <span class='cart-badge'><php echo $cart_badge; ?></span>
            </a>-->
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href='<?= BASE_URL ?>profile.php' class='icon-button' title='Profil'><i class='fa-solid fa-user'></i></a>
                <a href='<?= BASE_URL ?>views/logout.php' class='icon-button' title='Kijelentkezés'><i class='fa-solid fa-right-from-bracket'></i></a>
            <?php else: ?>
                <a href='<?= BASE_URL ?>views/login.php' class='icon-button' title='Bejelentkezés'><i class='fa-solid fa-user'></i></a>
                <a href='<?= BASE_URL ?>views/registration.php' class='icon-button-re' title='Regisztráció'>Regisztráció</a>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutLinks = document.querySelectorAll('a[href="<?= BASE_URL ?>views/logout.php"]'); 
            logoutLinks.forEach(link => {
                link.addEventListener("click", function(e) {
                    if (!confirm("Biztosan ki szeretnél jelentkezni?")) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</nav>

