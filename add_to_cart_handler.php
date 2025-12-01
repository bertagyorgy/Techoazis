<?php
session_start();
header('Content-Type: application/json');

// Ellenőrizzük, hogy POST kérés érkezett-e és megvan-e a termék ID
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kérés.']);
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen termék ID vagy mennyiség.']);
    exit;
}

// Inicializáljuk a kosarat, ha még nem létezik a session-ben
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hozzáadjuk a terméket a kosárhoz, vagy növeljük a darabszámot
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// Mivel ehhez a funkcióhoz szükség van a termékárakra és a nevére is, ezt le kellene kérnünk az adatbázisból.
// Jelenleg a termék nevének és árának ellenőrzése nem történik meg itt. 
// A cart.php fogja kezelni az adatbázis lekérdezést.

echo json_encode([
    'success' => true, 
    'message' => "A termék sikeresen hozzáadva a kosárhoz. Jelenlegi mennyiség: {$_SESSION['cart'][$product_id]}",
    'cart_count' => count($_SESSION['cart'])
]);
exit;
?>