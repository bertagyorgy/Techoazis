<?php
// product_card.php — Egyetlen termékkártya megjelenítése.

if (!isset($product) || !is_array($product)) {
    echo "<p>Hiba: Nincs termékadat a kártyához.</p>";
    return;
}

$product_id   = htmlspecialchars($product['product_id'] ?? 0);
$product_name = htmlspecialchars($product['product_name'] ?? 'Névtelen termék');
$price_value  = (float)($product['price'] ?? 0);
$price        = htmlspecialchars(number_format($price_value, 0, '', ' ') . ' HUF');

// Kép kezelés
$db_image = $product['main_image_url'] ?? '';
$placeholder_url = 'https://placehold.co/400x300/2d3357/FFFFFF?text=Techoazis';

if (strpos($db_image, 'http') === 0 || strpos($db_image, '//') === 0) {
    $base_url = $db_image; 
} elseif (!empty($db_image)) {
    $base_url = 'uploads/products/' . $db_image; 
} else {
    $base_url = $placeholder_url; 
}

$image_url = htmlspecialchars($base_url);
$stock = (int)($product['stock'] ?? 0); 
$is_available = $stock > 0;

// --- ÚJÍTÁS: Egyedi ID generálása a gombhoz, hogy a JS pontosan tudja, melyiket kell figyelni ---
$unique_btn_id = 'add-btn-' . $product_id;
?>

<div class="grid-col-4">
    <div class="custom-card reveal">
        <a href="product_detail.php?id=<?= $product_id ?>" style="display: block; position: relative; overflow: hidden; height: 220px;">
            <img src="<?= $image_url ?>"
                 alt="<?= $product_name ?>"
                 class="card-img-top"
                 onerror="this.onerror=null;this.src='https://placehold.co/400x260/2d3357/FFFFFF?text=Kép+hiba';"
                 style="width: 100%; height: 100%; object-fit: scale-down; transition: transform 0.5s ease;"
            >
            <?php if (!$is_available): ?>
                <div style="height: 50px; position: absolute; top: 100; left: 0; right: 0; bottom: 0; background-color: rgba(220, 53, 69, 0.7); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; font-weight: bold;">
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
                id="<?= $unique_btn_id ?>"
                data-product-id="<?= $product_id ?>"
                class="add-to-cart-btn"
                <?= $is_available ? '' : 'disabled' ?>
            >
                <i class="fas fa-shopping-cart"></i>
                <?= $is_available ? 'Kosárba' : 'Nincs raktáron' ?>
            </button>
        </div>
    </div>
</div>

<script>
    // JAVÍTÁS:
    // Nem querySelectorAll-t használunk, mert az minden eddigi gombot megtalálna újra és újra.
    // Helyette konkrétan csak ezt az EGY gombot keressük meg ID alapján.
    (function() {
        const btn = document.getElementById("<?= $unique_btn_id ?>");
        
        // Biztonsági ellenőrzés, és megnézzük, volt-e már rajta listener
        if (btn && !btn.dataset.listenerAdded) {
            btn.dataset.listenerAdded = "true"; // Megjelöljük, hogy bekötöttük

            btn.addEventListener("click", function () {
                const productId = this.dataset.productId;
                const originalText = this.innerHTML;

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Hozzáadás...';
                this.classList.add('fly-animation');

                setTimeout(() => {
                    fetch("add_to_cart_handler.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "product_id=" + productId
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.innerHTML = '<i class="fas fa-check"></i> Hozzáadva!';
                            this.style.backgroundColor = "var(--success-icon)";
                            this.style.color = "var(--text-color)";

                            // Frissítjük a jelvényt
                            const badges = document.querySelectorAll('.cart-badge');
                            badges.forEach(badge => badge.textContent = data.cart_count);
                        } else {
                            alert(data.message || "Hiba történt a kosárhoz adáskor!");
                            this.innerHTML = originalText;
                            this.disabled = false;
                        }
                    })
                    .catch(() => {
                        alert("Hiba történt a kosárhoz adáskor!");
                        this.innerHTML = originalText;
                        this.disabled = false;
                    });
                }, 800);
            });
        }
    })();
</script>