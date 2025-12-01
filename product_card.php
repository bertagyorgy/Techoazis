<?php
// product_card.php — Egyetlen termékkártya megjelenítése.

// Maximális biztonság érdekében ellenőrizzük a termékobjektum létét.
if (!isset($product) || !is_array($product)) {
    echo "<p>Hiba: Nincs termékadat a kártyához.</p>";
    return;
}

// Adatok biztonságos kinyerése
$product_id   = htmlspecialchars($product['product_id'] ?? 0);
$product_name = htmlspecialchars($product['product_name'] ?? 'Névtelen termék');
$price_value  = (float)($product['price'] ?? 0);
$price        = htmlspecialchars(number_format($price_value, 0, '', ' ') . ' HUF');

// KÉP ÚTVONAL JAVÍTÁSA: Kombinálva a helyes logika és a placeholder:
$db_image = $product['main_image_url'] ?? '';
$placeholder_url = 'https://placehold.co/400x300/2d3357/FFFFFF?text=Techoazis';

// A kép alap URL-jének meghatározása
if (strpos($db_image, 'http') === 0 || strpos($db_image, '//') === 0) {
    // Külső URL esetén használjuk azt.
    $base_url = $db_image; 
} elseif (!empty($db_image)) {
    // Lokális fájlnév esetén hozzáadjuk az 'images/' előtagot.
    $base_url = 'images/' . $db_image; 
} else {
    // Ha nincs kép az adatbázisban, használjuk a placeholder-t.
    $base_url = $placeholder_url; 
}

$image_url = htmlspecialchars($base_url);

$stock        = (int)($product['stock'] ?? 0);
$is_available = $stock > 0;
?>

<!-- VISSZAÁLLÍTVA: A korábbi, külső rácsrendszerre épülő CSS burkoló div. -->
<div class="grid-col-4">
    <div class="custom-card reveal">

        <!-- Kép és Részletek Link -->
        <a href="product_detail.php?id=<?= $product_id ?>" style="display: block; position: relative; overflow: hidden; height: 220px;">
            <img src="<?= $image_url ?>"
                 alt="<?= $product_name ?>"
                 class="card-img-top"
                 onerror="this.onerror=null;this.src='https://placehold.co/400x260/2d3357/FFFFFF?text=Kép+hiba';"
                 style="width: 100%; height: 100%; object-fit: scale-down; transition: transform 0.5s ease;"
            >
            
            <?php if (!$is_available): ?>
                <!-- Kifogyott Overlay -->
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(220, 53, 69, 0.7); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; font-weight: bold; transform: rotate(12deg); border: 4px solid white;">
                    KIFOGYOTT
                </div>
            <?php endif; ?>
        </a>

        <div class="card-body">
            <h5 class="card-title">
                <a href="product_detail.php?id=<?= $product_id ?>" style="color: var(--primary-color);">
                    <?= $product_name ?>
                </a>
            </h5>

            <p class="card-text" style="font-size: 1.4rem; font-weight: bold; color: var(--secondary-color);">
                <?= $price ?>
            </p>

            <p class="card-text">
                Készlet:
                <span style="font-weight: bold; color: <?= $is_available ? 'var(--success-icon)' : 'var(--danger-badge)' ?>;">
                    <?= $stock ?> db
                </span>
            </p>

            <button
                data-product-id="<?= $product_id ?>"
                class="add-to-cart-btn shopnow-small"
                style="margin-top: 10px; width: 100%;"
                <?= $is_available ? '' : 'disabled' ?>
            >
                <i class="fas fa-shopping-cart"></i>
                <?= $is_available ? 'Kosárba' : 'Nincs raktáron' ?>
            </button>
        </div>
    </div>
</div>