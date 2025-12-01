<?php
// Adatbázis kapcsolat inicializálása
include_once __DIR__ . '/app/db.php';

// Termékek lekérése
$products = [];
// JAVÍTÁS: Eltávolítottuk a WHERE stock_quantity > 0 feltételt,
// így a nulla készletű termékek is megjelennek, és a product_card.php
// fogja kezelni a "KIFOGYOTT" állapot megjelenítését.
$sql = "SELECT product_id, product_name, price, main_image_url, stock_quantity 
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
    $error_message = "Hiba: Az adatbázis kapcsolat (\$conn) nem inicializált vagy érvénytelen.";
}

// Navbar + assetek
include './views/navbar.php';
?>
<link rel="stylesheet" href="./static/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
<script src="./static/index.js" defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

<div class="gap"></div>

<section class="section-padding">
    <div class="custom-container text-center">
        <h1 class="section-title">Termékkatalógus</h1>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <strong class="font-bold">Adatbázis hiba: </strong> <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <p class="text-2xl text-gray-600">Jelenleg nincsenek elérhető termékek.</p>
        <?php else: ?>

            <div class="grid-row">
                <?php foreach ($products as $product): ?>

                    <?php include 'product_card.php'; ?>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</section>

