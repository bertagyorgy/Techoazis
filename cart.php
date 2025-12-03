<?php
session_start();
include_once __DIR__ . '/app/db.php';
include './views/navbar.php';

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
    exit;
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
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoazis | Cart</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/index.js" defer></script>
</head>
<style>
body{
    background-color: #eae3c9;
}
/* Egyedi CSS a kosárhoz, hogy jobban nézzen ki */
.cart-table th, .cart-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

/* --- Modern Kosár Design --- */

.cart-container {
    max-width: 1200px;
    margin: auto;
}

.cart-card {
    background: white;
    padding: 1.75rem;
    border-radius: 16px;
    box-shadow: 0px 4px 20px rgba(0,0,0,0.08);
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table thead {
    background: #f1f3f5;
}

.cart-table th {
    padding: 16px;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.9rem;
    border-bottom: 2px solid #dee2e6;
}

.cart-table td {
    padding: 18px 16px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.cart-table tr:hover {
    background: #f8f9fa;
}

.cart-product-img {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    object-fit: contain;
    background: #fff;
    border: 1px solid #e0e0e0;
}

/* Mennyiség input */
.quantity-input {
    width: 70px;
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #bbb;
    text-align: center;
    font-size: 1rem;
}

.quantity-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Törlés ikon */
.delete-btn {
    color: #d63031;
    font-size: 1.4rem;
    cursor: pointer;
    transition: 0.2s;
}
.delete-btn:hover {
    color: #ff7675;
}

/* Összegző box */
.total-box {
    background: white;
    padding: 1.75rem;
    border-radius: 16px;
    box-shadow: 0px 4px 20px rgba(0,0,0,0.08);
    width: 350px;

    /* ÚJ – ez húzza lejjebb */
    margin-top: 40px;
}


.total-price {
    font-size: 2rem;
    font-weight: 800;
    color: var(--danger-badge);
}

/* Checkout gomb */
.checkout-btn {
    display: block;
    text-align: center;
    background: var(--primary-color);
    color: white;
    padding: 16px 0;
    font-size: 1.2rem;
    font-weight: 700;
    border-radius: 50px;
    margin-top: 20px;
    transition: 0.3s;
}
.checkout-btn:hover {
    background: var(--secondary-color);
}
.back-to-shop-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    background-color: #e9ecef;
    color: #0d6efd;
    padding: 12px 22px;

    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;

    transition: all 0.25s ease;
    text-decoration: none;
    border: 1px solid #d0d7dd;
}

.back-to-shop-btn:hover {
    background-color: #dbe2e7;
    color: #0a58ca;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.back-to-shop-wrapper {
    margin-top: 40px;
    margin-bottom: 30px;
}

.back-to-shop-btn {
    width: 100%;
    justify-content: center;
}
/* ===============================
   MOBILOS KOSÁR NÉZET
================================*/
@media (max-width: 768px) {

    .cart-table,
    .cart-table thead,
    .cart-table tbody,
    .cart-table tr,
    .cart-table td,
    .cart-table th {
        display: block;
        box-sizing: border-box;
    }

    .cart-table tr {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0px 2px 12px rgba(0,0,0,0.08);
    }

    .cart-table td {
        padding: 10px 0;
        border: none;
    }

    .cart-product-img {
        width: 100%;
        height: auto;
        margin-bottom: 10px;
    }

    .total-box {
        width: 100%;
    }

    .quantity-input {
        width: 100%;
    }

    .delete-btn {
        float: right;
        margin-top: -5px;
    }
}


</style>
<body>
<div class="gap"></div>

<section class="section-padding">
    <div class="cart-container">

        <h1 class="text-center text-4xl font-bold mb-10 text-gray-800">🛒 A te kosarad</h1>

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
                                <a href="product_detail.php?id=<?= $item['id'] ?>" class="text-blue-600 font-semibold hover:underline">
                                    <?= $item['name'] ?>
                                </a>
                            </td>

                            <td class="text-center font-medium">
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

                            <td class="text-center font-bold text-lg text-gray-800">
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
                <h2 class="text-xl font-semibold mb-4">Végösszeg</h2>

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
