<?php
session_start();
include_once __DIR__ . '/app/db.php';
include './views/navbar.php';

// --- Kosár Tartalmának Kezelése ---

// Kezeljük a kosár frissítését/törlését, ha POST kérés érkezett
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Termék Eltávolítása (Törlés) ---
    if (isset($_POST['remove_item']) && isset($_POST['product_id'])) {
        $product_id_to_remove = (int)$_POST['product_id'];
        // Törlés megkísérlése a sessionből
        unset($_SESSION['cart'][$product_id_to_remove]);
    
    // --- 2. Mennyiség Frissítése ---
    // Akkor fut le, ha a "quantity" tömb el lett küldve.
    } elseif (isset($_POST['quantity']) && is_array($_POST['quantity']) && isset($conn) && $conn instanceof mysqli) {
        $updated_quantities = $_POST['quantity'];
        $product_ids_to_check = array_keys($updated_quantities);
        
        if (!empty($product_ids_to_check)) {
            // Lekérdezzük a raktárkészletet az adatbázisból
            $placeholders = implode(',', array_fill(0, count($product_ids_to_check), '?'));
            // FIGYELEM: A helyes működéshez a 'stock_quantity' oszlopnak léteznie kell az adatbázisban!
            $sql_stock = "SELECT product_id, stock_quantity FROM products WHERE product_id IN ($placeholders)";
            
            $stmt_stock = $conn->prepare($sql_stock);
            $types = str_repeat('i', count($product_ids_to_check));
            $stmt_stock->bind_param($types, ...$product_ids_to_check);
            $stmt_stock->execute();
            $result_stock = $stmt_stock->get_result();
            
            $stock_data = [];
            while ($row = $result_stock->fetch_assoc()) {
                $stock_data[$row['product_id']] = (int)$row['stock_quantity'];
            }

            // Ellenőrizzük a kért mennyiséget a valós raktárkészlettel szemben
            foreach ($updated_quantities as $product_id => $quantity) {
                $product_id = (int)$product_id;
                $quantity = (int)$quantity;

                $max_stock = $stock_data[$product_id] ?? 0;
                
                if ($quantity > 0) {
                    if ($quantity > $max_stock) {
                        // Készletkorlát túllépve, korrigáljuk.
                        $_SESSION['cart'][$product_id] = $max_stock;
                    } else {
                        // Frissítjük a sessiont
                        $_SESSION['cart'][$product_id] = $quantity;
                    }
                } else {
                    // Ha a mennyiség 0, töröljük a terméket a kosárból
                    unset($_SESSION['cart'][$product_id]);
                }
            }
        }
    }
    
    // Átirányítás, hogy elkerüljük az űrlap újbóli elküldését (F5 probléma)
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total_price = 0;

if (!empty($cart) && isset($conn) && $conn instanceof mysqli) {
    // Kinyerjük a termék ID-ket a sessionből egy SQL lekérdezéshez
    $product_ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    // Lekérdezzük az összes termék adatát és a készletet
    $sql = "SELECT product_id, product_name, price, main_image_url, stock_quantity FROM products WHERE product_id IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    $types = str_repeat('i', count($product_ids));
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $products_data = [];
    while ($row = $result->fetch_assoc()) {
        $products_data[$row['product_id']] = $row;
    }
    
    // Összeállítjuk a megjelenítendő kosár adatokat
    foreach ($cart as $product_id => $quantity) {
        if (isset($products_data[$product_id])) {
            $product = $products_data[$product_id];
            
            $current_stock = (int)$product['stock_quantity'];

            // Készlet ellenőrzése a megjelenítés előtt
            if ($quantity > $current_stock) {
                 $quantity = $current_stock;
                 $_SESSION['cart'][$product_id] = $quantity;
            }

            $subtotal = $product['price'] * $quantity;
            $total_price += $subtotal;
            
            // Kép URL logikája 
            $db_image = $product['main_image_url'] ?? '';
            if (strpos($db_image, 'http') === 0 || strpos($db_image, '//') === 0) {
                $image_url = $db_image;
            } elseif (!empty($db_image)) {
                $image_url = 'images/' . $db_image; 
            } else {
                $image_url = 'https://placehold.co/80x80/2d3357/FFFFFF?text=Kép';
            }
            
            $cart_items[] = [
                'id' => $product_id,
                'name' => htmlspecialchars($product['product_name']),
                'price' => (float)$product['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'image_url' => htmlspecialchars($image_url),
                'stock' => $current_stock 
            ];
        } else {
            // A termék már nem létezik az adatbázisban, eltávolítjuk a kosárból
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

// --- HTML Megjelenítés ---
?>
<link rel="stylesheet" href="./static/index.css">
<!-- Font Awesome betöltése az ikon megjelenítéséhez -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
<style>
/* Egyedi CSS a kosárhoz, hogy jobban nézzen ki */
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css");
.cart-table th, .cart-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.cart-table th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    text-transform: uppercase;
}
.cart-total-box {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
}
.checkout-btn {
    display: inline-block;
    padding: 12px 25px;
    background-color: var(--primary-color);
    color: white;
    border-radius: 50px;
    text-align: center;
    font-weight: bold;
    transition: background-color 0.3s;
}
.checkout-btn:hover {
    background-color: var(--secondary-color);
}
.quantity-input {
    width: 60px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 5px;
}
</style>

<div class="gap"></div>

<section class="section-padding">
    <div class="custom-container">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">Kosár tartalma</h1>

        <?php if (empty($cart_items)): ?>
            <div class="text-center p-10 bg-white rounded-lg shadow-lg">
                <p class="text-xl text-gray-600">A kosarad jelenleg üres. Látogass el a <a href="shop.php" class="text-blue-500 hover:underline font-semibold">Termékkatalógusba</a>!</p>
            </div>
        <?php else: ?>
            
            <!-- Űrlap a mennyiség frissítéséhez (a frissítés automatikusan elindul a JavaScript onchange eseményével) -->
            <form method="POST" action="cart.php" id="quantityForm">
                <div class="overflow-x-auto bg-white rounded-lg shadow-lg mb-8">
                    <table class="w-full cart-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Termék</th>
                                <th>Ár</th>
                                <th>Mennyiség</th>
                                <th>Részösszeg</th>
                                <th>Törlés</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td class="w-20"><img src="<?= $item['image_url'] ?>" alt="<?= $item['name'] ?>" class="rounded-lg w-full h-auto"></td>
                                    <td><a href="product_detail.php?id=<?= $item['id'] ?>" class="text-blue-600 hover:underline"><?= $item['name'] ?></a></td>
                                    <td><?= number_format($item['price'], 0, '', ' ') ?> HUF</td>
                                    <td>
                                        <!-- Mennyiség beviteli mező, automatikus frissítéssel (onchange) és MAX készlettel -->
                                        <input type="number" 
                                            name="quantity[<?= $item['id'] ?>]" 
                                            value="<?= $item['quantity'] ?>" 
                                            min="1" 
                                            max="<?= $item['stock'] ?>" 
                                            class="quantity-input" 
                                            onchange="this.form.submit()"
                                        >
                                    </td>
                                    <td><?= number_format($item['subtotal'], 0, '', ' ') ?> HUF</td>
                                    
                                    <!-- Minden törlés gombhoz saját, különálló űrlap. -->
                                    <td>
                                        <!-- KÜLÖN FORM A TÖRLÉSHEZ, EZZEL BIZTOSÍTVA VAN, HOGY MINDIG MŰKÖDJÖN -->
                                        <form method="POST" action="cart.php" style="display:inline;">
                                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                            <button type="submit" name="remove_item" value="1" class="text-red-500 hover:text-red-700 font-bold">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <!-- Üres div a térköz miatt -->
                </div>
            </form>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <a href="shop.php" class="text-blue-500 hover:underline mb-4 md:mb-0"><i class="fas fa-arrow-left mr-2"></i>Folytatom a vásárlást</a>
                
                <div class="cart-total-box w-full md:w-1/3">
                    <div class="flex justify-between font-bold text-xl mb-4">
                        <span>Összesen:</span>
                        <span class="text-2xl text-red-600"><?= number_format($total_price, 0, '', ' ') ?> HUF</span>
                    </div>
                    <a href="checkout.php" class="checkout-btn w-full block text-center">Tovább a pénztárhoz</a>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>