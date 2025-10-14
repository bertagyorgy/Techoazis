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
<nav class="navbar navbar-expand-lg navbar-light bg-light" style="font-weight:bold; border-bottom: 3px solid #000">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="images/techoazis_logo_chopped.png" style="height:35px;width:200px"alt="kep">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="index.php">Főoldal</a></li>
                <li class="nav-item"><a class="nav-link" href="articles.php">Tudástár</a></li>
                <li class="nav-item"><a class="nav-link" href="projects.php">Projektek</a></li>
                <li class="nav-item"><a class="nav-link" href="forum.php">Közösség</a></li>
                <li class="nav-item"><a class="nav-link" href="shop.php">Vásárlás</a></li>
                <!--?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <li class="nav-item"><a class="nav-link" href="upload.php">Feltöltés</a></li>
                    <li class="nav-item"><a class="nav-link" href="management.php">Kezelés</a></li>
                ?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="contact.php">Kapcsolat</a></li> HA BEJELENTKEZTÜNK-->
            </ul>
        </div>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <form class='d-flex align-items-center gap-3'>
                <a href='shop.php' class='icon-button ms-4'><i class='fa-solid fa-magnifying-glass fs-5'></i></a>
                <a href='cart.php' class='icon-button ms-4 position-relative'>
                    <i class='fa-solid fa-cart-shopping fs-5'></i>
                    <span class='badge bg-danger position-absolute top-0 start-100 translate-middle'>
                        <?php echo $cart_badge; ?>
                    </span>
                </a>
                <a href='profile.php' class='icon-button ms-4'><i class='fa-solid fa-user fs-5'></i></a>
            </form>
        <?php else: ?>
            <form class='d-flex align-items-center gap-3'>
                <a href='shop.php' class='icon-button ms-4'><i class='fa-solid fa-magnifying-glass fs-5'></i></a>
                <a href='login.php' class='icon-button ms-4'><i class='fa-solid fa-user fs-5'></i></a>
            </form>
        <?php endif; ?>
    </div>
</nav>


