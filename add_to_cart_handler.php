<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Érvénytelen kérés.'
    ]);
    exit;
}

$product_id = (int)$_POST['product_id'];
if ($product_id <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Érvénytelen termék ID.'
    ]);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hozzáadás
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += 1;
} else {
    $_SESSION['cart'][$product_id] = 1;
}

// JAVÍTÁS: Nem az összes mennyiség (array_sum), hanem a különböző termékek száma (count)
// Így a navbar jelvénye kattintás után is konzisztens marad.
$unique_items_count = count($_SESSION['cart']);

echo json_encode([
    'success'      => true,
    'message'      => 'A termék sikeresen a kosárba került.',
    'added_id'     => $product_id,
    'new_quantity' => $_SESSION['cart'][$product_id],
    'cart_count'   => $unique_items_count // Ez frissíti a jelvényt
]);
exit;