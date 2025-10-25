<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db.php';

// Ha be vagyunk jelentkezve, kérdezzük le a kosár tartalmát
$cart_badge = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $cart_count_result = $conn->query("SELECT SUM(quantity) AS total_items FROM cart");
    $cart_count = $cart_count_result->fetch_assoc()['total_items'] ?? 0;
    $cart_badge = $cart_count > 0 ? $cart_count : '0';
}
?>
<!-- TO DO: DOM TARTALOM BETÖLTÉS ITT A NAVBARON KERESZTÜL, ÍGY MEGSZŰNIK A VILLANÁS-->
<nav class="main-navbar">
    <div class="custom-container nav-container">
        <a class="nav-brand" href="#">
            <img src="images/techoazis_logo_chopped.png" alt="kep">
        </a>
        
        <button class="nav-toggler" id="navToggle" aria-label="Menü">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <div class="nav-collapse" id="navCollapseContent">
            <ul class="nav-menu">
                <li class="nav-item"><a class="nav-link active" href="index.php">Főoldal</a></li>
                <li class="nav-item"><a class="nav-link" href="articles.php">Tudástár</a></li>
                <li class="nav-item"><a class="nav-link" href="projects.php">Projektek</a></li>
                <li class="nav-item"><a class="nav-link" href="forum.php">Közösség</a></li>
                <li class="nav-item"><a class="nav-link" href="shop.php">Vásárlás</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Kapcsolat</a></li>
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                    <li class="nav-item"><a class="nav-link" href="admin_panel.php" >Admin</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-icons mobile-icons">
                <a href='shop.php' class='icon-button' title='Keresés'><i class='fa-solid fa-magnifying-glass'></i></a>
                <a href='cart.php' class='icon-button cart-icon' title='Kosár'>
                    <i class='fa-solid fa-cart-shopping'></i>
                    <span class='cart-badge'>0<!--?php echo $cart_badge; ?>--></span>
                </a>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <a href='profile.php' class='icon-button' title='Profil'><i class='fa-solid fa-user'></i></a>
                    <a href="logout.php" class="icon-button" title="Kijelentkezés">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                <?php else: ?>  
                    <a href="login.php" class="icon-button" title='Bejelentkezés'><i class="fa-solid fa-user"></i></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="nav-icons desktop-icons">
            <a href='shop.php' class='icon-button' title='Keresés'><i class='fa-solid fa-magnifying-glass'></i></a>
            <a href='cart.php' class='icon-button cart-icon' title='Kosár'>
                <i class='fa-solid fa-cart-shopping'></i>
                <span class='cart-badge'>0<!--?php echo $cart_badge; ?>--></span>
            </a>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href='profile.php' class='icon-button' title='Profil'><i class='fa-solid fa-user'></i></a>
                <a href='logout.php' class='icon-button' title='Kijelentkezés'><i class='fa-solid fa-right-from-bracket'></i></a>
            <?php else: ?>  
                <a href='login.php' class='icon-button' title='Bejelentkezés'><i class='fa-solid fa-user'></i></a>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Minden logout linkre ráteszi az eseményt (mobil + desktop)
            const logoutLinks = document.querySelectorAll('a[href="logout.php"]');
            logoutLinks.forEach(link => {
                link.addEventListener("click", function(e) {
                    const confirmed = confirm("Biztosan ki szeretnél jelentkezni?");
                    if (!confirmed) {
                        e.preventDefault(); // ha nem nyom 'OK'-t, akkor nem lép ki
                    }
                });
            });
        });
    </script>

</nav>