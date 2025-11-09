<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEFINIÁLÁS: A PROJEKT GYÖKÉR ELÉRÉSI ÚTJA A BÖNGÉSZŐ SZÁMÁRA
// Ezt az útvonalat kell használnod a XAMPP-ben (htdocs-tól számítva):
$root = '/sulisprojektek/Techoazis/'; 

// JAVÍTÁS: PHP include abszolút elérési úttal (EZ HELYES!)
include_once __DIR__ . '/../app/db.php';

// Ha be vagyunk jelentkezve, kérdezzük le a kosár tartalmát
$cart_badge = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($conn)) {
        $cart_count_result = $conn->query("SELECT SUM(quantity) AS total_items FROM cart");
        $cart_count = $cart_count_result->fetch_assoc()['total_items'] ?? 0;
        $cart_badge = $cart_count > 0 ? $cart_count : '0';
    } else {
        $cart_badge = '0';
    }
}
?>
<nav class="main-navbar">
    <div class="custom-container nav-container">
        <a class="nav-brand" href="<?= $root ?>index.php"> 
            <img src="<?= $root ?>images/techoazis_logo_chopped.png" alt="kep">
        </a>
        
        <button class="nav-toggler" id="navToggle" aria-label="Menü">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <div class="nav-collapse" id="navCollapseContent">
            <ul class="nav-menu">
                <li class="nav-item"><a class="nav-link active" href="<?= $root ?>index.php">Főoldal</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $root ?>articles.php">Tudástár</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $root ?>projects.php">Projektek</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $root ?>forum.php">Közösség</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $root ?>shop.php">Vásárlás</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $root ?>contact.php">Kapcsolat</a></li>
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $root ?>admin/admin.php" >Admin</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-icons mobile-icons">
                <a href='<?= $root ?>shop.php' class='icon-button' title='Keresés'><i class='fa-solid fa-magnifying-glass'></i></a>
                <a href='<?= $root ?>cart.php' class='icon-button cart-icon' title='Kosár'>
                    <i class='fa-solid fa-cart-shopping'></i>
                    <span class='cart-badge'>0<!--?php echo $cart_badge; ?--></span>
                </a>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <a href='<?= $root ?>profile.php' class='icon-button' title='Profil'><i class='fa-solid fa-user'></i></a>
                    <a href="<?= $root ?>views/logout.php" class="icon-button" title="Kijelentkezés"> 
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                <?php else: ?>  
                    <a href="<?= $root ?>views/login.php" class="icon-button" title='Bejelentkezés'><i class="fa-solid fa-user"></i></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="nav-icons desktop-icons">
            <a href='<?= $root ?>shop.php' class='icon-button' title='Keresés'><i class='fa-solid fa-magnifying-glass'></i></a>
            <a href='<?= $root ?>cart.php' class='icon-button cart-icon' title='Kosár'>
                <i class='fa-solid fa-cart-shopping'></i>
                <span class='cart-badge'>0<!--?php echo $cart_badge; ?--></span>
            </a>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href='<?= $root ?>profile.php' class='icon-button' title='Profil'><i class='fa-solid fa-user'></i></a>
                <a href='<?= $root ?>views/logout.php' class='icon-button' title='Kijelentkezés'><i class='fa-solid fa-right-from-bracket'></i></a>
            <?php else: ?>  
                <a href='<?= $root ?>views/login.php' class='icon-button' title='Bejelentkezés'><i class='fa-solid fa-user'></i></a>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // JAVÍTÁS: A JS-ben a href-et most már abszolút útvonallal kell keresni
            const logoutLinks = document.querySelectorAll('a[href="<?= $root ?>views/logout.php"]'); 
            logoutLinks.forEach(link => {
                link.addEventListener("click", function(e) {
                    const confirmed = confirm("Biztosan ki szeretnél jelentkezni?");
                    if (!confirmed) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>

</nav>