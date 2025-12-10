<?php
// Adatbázis kapcsolat inicializálása
include_once __DIR__ . '/app/db.php';

// Termékek lekérése
$products = [];
// JAVÍTÁS: Eltávolítottuk a WHERE stock_quantity > 0 feltételt,
// így a nulla készletű termékek is megjelennek, és a product_card.php
// fogja kezelni a "KIFOGYOTT" állapot megjelenítését.
$sql = "SELECT product_id, product_name, price, main_image_url, stock
        FROM products 
        ORDER BY product_name ASC";

if (isset($conn) && $conn instanceof mysqli) {
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    $error_message = "Hiba: Az adatbázis kapcsolat nem inicializált vagy érvénytelen.";
}


?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoazis | Shop</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/comments.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="./static/create_post.css">
    <link rel="stylesheet" href="./static/custom_card.css">
    <link rel="stylesheet" href="./static/feature_cards.css">
    <link rel="stylesheet" href="./static/filter_system.css">
    <link rel="stylesheet" href="./static/forum.css">
    <link rel="stylesheet" href="./static/group_view.css">
    <link rel="stylesheet" href="./static/hero_section.css">
    <link rel="stylesheet" href="./static/loading_animation.css">
    <link rel="stylesheet" href="./static/login_page.css">
    <link rel="stylesheet" href="./static/modern_footer.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/post_card.css">
    <link rel="stylesheet" href="./static/profile_pages.css">
    <link rel="stylesheet" href="./static/responsive_adjustments.css">
    <link rel="stylesheet" href="./static/utility_classes.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/index.js" defer></script>
</head>
<style>
    body{
        background-color: var(--background);
    }
    .custom-container {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
        box-sizing: border-box;
    }

    /* Különböző képernyőméretekhez a konténer maximális szélessége */
    @media (min-width: 576px) {
        .section-title { font-size: 2.5rem; }
        .custom-container { max-width: 540px; }
    }
    @media (min-width: 768px) { .custom-container { max-width: 720px; } }
    @media (min-width: 992px) { .custom-container { max-width: 960px; } }
    @media (min-width: 1200px) { .custom-container { max-width: 1140px; } }

    .grid-row {
        display: flex;
        flex-wrap: wrap;
        margin: -1rem; /* Negatív margó a távolságokhoz */
    }
    /* ===============================
   FILTER + PRODUCT LAYOUT 
    ================================*/
    .shop-layout {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }

    .shop-sidebar {
        width: 260px;
        flex-shrink: 0;
    }

    .shop-content {
        flex: 1;
    }

    /* Reszponzív: telefonon alulról felülre rendeződik */
    @media (max-width: 992px) {
        .shop-layout {
            flex-direction: column;
        }

        .shop-sidebar {
            width: 100%;
        }

        .shop-content {
            width: 100%;
        }
    }
</style>
<body>
<?php include './views/navbar.php'; ?>

<section class="section-padding">
    <div class="custom-container">

        <div class="shop-layout">

            <!-- =============================== -->
            <!--          ASIDE SZŰRŐ            -->
            <!-- =============================== -->
            <aside class="shop-sidebar">

                <div class="filter-section">

                    <div class="filter-header">
                        <h3 style="color:var(--text-color);">Szűrők</h3>
                    </div>

                    <!-- Keresőmező -->
                    <div class="filter-group">
                        <label for="search">Keresés</label>
                        <input type="text" id="search" class="filter-select" placeholder="Termék neve...">
                    </div>

                    <!-- Kategória -->
                    <div class="filter-group">
                        <label for="category">Kategória</label>
                        <select id="category" class="filter-select">
                            <option value="">Összes kategória</option>
                            <option value="pc">PC</option>
                            <option value="kiegészítő">Kiegészítők</option>
                            <option value="monitor">Monitorok</option>
                            <option value="egyéb">Egyéb</option>
                        </select>
                    </div>

                    <!-- Ár -->
                    <div class="filter-group">
                        <label>Ár</label>
                        <div class="filter-tags">
                            <div class="filter-tag">0-10 000 Ft</div>
                            <div class="filter-tag">10 000-50 000 Ft</div>
                            <div class="filter-tag">50 000-100 000 Ft</div>
                            <div class="filter-tag">100 000 Ft+</div>
                        </div>
                    </div>

                    <!-- Készlet -->
                    <div class="filter-group">
                        <label>Készlet</label>
                        <div class="filter-tags">
                            <div class="filter-tag">Raktáron</div>
                            <div class="filter-tag">Kifogyott</div>
                        </div>
                    </div>

                    <!-- Gombok -->
                    <div class="filter-actions">
                        <button class="btn" style="background:var(--accent-600); color:white;">Szűrés</button>
                        <button class="btn" style="background:var(--text-color); color:var(--background);">Törlés</button>
                    </div>

                </div>

            </aside>

            <!-- =============================== -->
            <!--          TERMÉK LISTA          -->
            <!-- =============================== -->
            <div class="shop-content">

                <h1 class="section-title text-center">Termékkatalógus</h1>

                <?php if (isset($error_message)): ?>
                    <div class="error-box">
                        <strong>Adatbázis hiba: </strong> <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($products)): ?>
                    <p class="text-2xl text-gray-600 text-center">
                        Jelenleg nincsenek elérhető termékek.
                    </p>
                <?php else: ?>

                    <div class="grid-row">
                        <?php foreach ($products as $product): ?>
                            <?php include 'product_card.php'; ?>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</section>


</body>
</html>

