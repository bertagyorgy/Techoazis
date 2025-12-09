<?php
session_start();
include_once __DIR__ . '/app/db.php';


// --- Kosár műveletek ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Termék törlése
    if (isset($_POST['remove_item']) && isset($_POST['product_id'])) {
        unset($_SESSION['cart'][(int)$_POST['product_id']]);

    } elseif (isset($_POST['quantity']) && is_array($_POST['quantity']) && isset($conn)) {

        $updated_quantities = $_POST['quantity'];
        $ids = array_keys($updated_quantities);

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "SELECT product_id, stock_quantity FROM products WHERE product_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();

            $stock_data = [];
            while ($row = $result->fetch_assoc()) {
                $stock_data[$row['product_id']] = (int)$row['stock_quantity'];
            }

            foreach ($updated_quantities as $pid => $qty) {
                $pid = (int)$pid;
                $qty = (int)$qty;
                $max = $stock_data[$pid] ?? 0;

                if ($qty <= 0) {
                    unset($_SESSION['cart'][$pid]);
                } elseif ($qty > $max) {
                    $_SESSION['cart'][$pid] = $max;
                } else {
                    $_SESSION['cart'][$pid] = $qty;
                }
            }
        }
    }

    header("Location: cart.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total_price = 0;

if (!empty($cart) && isset($conn)) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "SELECT product_id, product_name, price, main_image_url, stock_quantity 
            FROM products WHERE product_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[$row['product_id']] = $row;
    }

    foreach ($cart as $pid => $qty) {
        if (!isset($products[$pid])) {
            unset($_SESSION['cart'][$pid]);
            continue;
        }

        $p = $products[$pid];

        if ($qty > $p['stock_quantity']) {
            $qty = $p['stock_quantity'];
            $_SESSION['cart'][$pid] = $qty;
        }

        $subtotal = $p['price'] * $qty;
        $total_price += $subtotal;

        $img = $p['main_image_url'];
        if (!$img) {
            $img = "https://placehold.co/100x100?text=Nincs+kép";
        } elseif (!str_starts_with($img, 'http')) {
            $img = 'uploads/products/' . $img;
        }

        $cart_items[] = [
            'id' => $pid,
            'name' => htmlspecialchars($p['product_name']),
            'price' => (float)$p['price'],
            'quantity' => $qty,
            'subtotal' => $subtotal,
            'stock' => (int)$p['stock_quantity'],
            'image_url' => htmlspecialchars($img)
        ];
    }
}
include './views/navbar.php';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoazis | Cart</title>
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
.item-name-text{
    color: var(--item-name-text);
}
/* Egyedi CSS a kosárhoz, hogy jobban nézzen ki */
.cart-table th, .cart-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    color: var(--item-name-text);
}

/* --- Modern Kosár Design --- */

.cart-container {
    max-width: 1200px;
    margin: auto;
}

.cart-card {
    background: var(--border-color);
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0px 8px 32px rgba(0,0,0,0.12), 0px 2px 8px rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.06);
    overflow: hidden;
}

.cart-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.cart-table thead {
    background: linear-gradient(135deg, var(--admin-secondary), var(--admin-primary));
    position: sticky;
    top: 0;
    z-index: 10;
}

.cart-table th {
    padding: 20px 16px;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    color: var(--neutral-100);
    border-bottom: 2px solid #cbd5e1;
    position: relative;
}

.cart-table th:first-child {
    border-top-left-radius: 12px;
}

.cart-table th:last-child {
    border-top-right-radius: 12px;
}

.cart-table td {
    padding: 24px 16px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
    transition: background-color 0.2s ease;
    background-color: var(--text-light-o);
}

.cart-table tbody tr {
    background: white;
    transition: all 0.2s ease;
}


.cart-table tbody tr:last-child td {
    border-bottom: none;
}

.cart-product-img {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: contain;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 2px solid rgba(255,255,255,0.8);
    box-shadow: 0px 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}

.cart-product-img:hover {
    transform: scale(1.05);
    box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
}

/* Mennyiség input */
.quantity-input {
    width: 80px;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    text-align: center;
    font-size: 1rem;
    background: #ffffff;
    transition: all 0.2s ease;
    color: #1e293b;
}

.quantity-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0px 0px 0px 3px rgba(37, 99, 235, 0.1);
    background: #ffffff;
}

.quantity-input:hover {
    border-color: #3b82f6;
}

/* Törlés ikon */
.delete-btn {
    color: #d63031;
    font-size: 1.3rem;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 8px;
    border-radius: 6px;
    background: transparent;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.delete-btn:hover {
    color: #b91c1c;
    background: #fee2e2;
    transform: scale(1.1);
}

/* Összegző box */
.total-box {
    background: var(--border-color);
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0px 8px 32px rgba(0,0,0,0.12), 0px 2px 8px rgba(0,0,0,0.08);
    width: 100%;
    max-width: 400px;
    border: 1px solid rgba(0,0,0,0.06);
    margin-top: 2rem;
}

.total-price {
    font-size: 2.2rem;
    color: #dc2626;
    margin: 1rem 0;
}

/* Checkout gomb */
.checkout-btn {
    display: block;
    text-align: center;
    background: var(--primary-700)/*linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)*/;
    color: white;
    padding: 18px 0;
    font-size: 1.15rem;
    border-radius: 12px;
    margin-top: 1.5rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0px 4px 16px rgba(37, 99, 235, 0.3);
    text-decoration: none;
}

.checkout-btn:hover {
    background: var(--primary-500);
    transform: translateY(-2px);
    box-shadow: 0px 6px 20px rgba(37, 99, 235, 0.4);
    color: var(--neutral-100);
}

.back-to-shop-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: #475569;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    box-shadow: 0px 2px 8px rgba(0,0,0,0.06);
}

.back-to-shop-btn:hover {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    color: #334155;
    transform: translateY(-2px);
    box-shadow: 0px 4px 16px rgba(0,0,0,0.12);
}

.back-to-shop-wrapper {
    margin-top: 2rem;
    margin-bottom: 2rem;
}

</style>
<body>
<section class="section-padding">
    <div class="cart-container">

        <h1 class="text-center text-4xl font-bold mb-10 text-gray-800">A te kosarad</h1><br>

        <?php if (empty($cart_items)): ?>
            
            <div class="cart-card text-center py-12">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">A kosár üres</h2>
                <p class="text-gray-500">Nézz körül a boltban és adj hozzá pár terméket!</p>
                <a href="shop.php" class="checkout-btn w-72 mx-auto mt-6">Vissza a boltba</a>
            </div>

        <?php else: ?>

        <form method="POST" action="cart.php">
            <div class="cart-card overflow-x-auto">

                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Termék</th>
                            <th>Terméknév</th>
                            <th class="text-center">Ár</th>
                            <th class="text-center">Mennyiség</th>
                            <th class="text-center">Részösszeg</th>
                            <th class="text-center">Törlés</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <a href="product_detail.php?id=<?= $item['id'] ?>">
                                    <img src="<?= $item['image_url'] ?>" class="cart-product-img" alt="<?= $item['name'] ?>"
                                        onerror="this.src='https://placehold.co/90x90?text=Hiba';">
                                </a>
                            </td>

                            <td>
                                <h4>
                                    <a href="product_detail.php?id=<?= $item['id'] ?>" class="item-name-text"><?= $item['name'] ?></a>
                                </h4>
                            </td>

                            <td style="font-size: 1.25rem; font-weight: 600;">
                                <?= number_format($item['price'], 0, '', ' ') ?> Ft
                            </td>

                            <td class="text-center">
                                <input type="number" 
                                       name="quantity[<?= $item['id'] ?>]" 
                                       min="1" 
                                       max="<?= $item['stock'] ?>"
                                       value="<?= $item['quantity'] ?>"
                                       onchange="this.form.submit()"
                                       class="quantity-input">

                                <?php if ($item['quantity'] == $item['stock']): ?>
                                    <p class="text-xs text-red-500 mt-1">Maximális készlet!</p>
                                <?php endif; ?>
                            </td>

                            <td class="text-center font-bold text-lg" style="font-size: 1.25rem; font-weight: 600;">
                                <?= number_format($item['subtotal'], 0, '', ' ') ?> Ft
                            </td>

                            <td class="text-center">
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button class="delete-btn" name="remove_item" value="1">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>
        </form>

        <div class="flex flex-col md:flex-row justify-between mt-10">

            <div class="total-box">
                <h2>Végösszeg:</h2>

                <p class="total-price mb-6">
                    <?= number_format($total_price, 0, '', ' ') ?> Ft
                </p>

                <!-- Vissza a vásárláshoz gomb IDE került -->
                <a href="shop.php" class="back-to-shop-btn mb-4">
                    <i class="fas fa-arrow-left"></i>
                    Folytatom a vásárlást
                </a>

                <!-- Checkout gomb marad ugyanitt -->
                <a href="checkout.php" class="checkout-btn">
                    Tovább a fizetéshez
                </a>
            </div>

        </div>


        <?php endif; ?>

    </div>
</section>



</body>
</html>