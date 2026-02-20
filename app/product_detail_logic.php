<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Biztosítjuk, hogy a config be legyen töltve, ha önmagában futna
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

$product_id = $_GET['id'] ?? 0;

// Termék adatok lekérése
$sql = "SELECT p.*, u.username as seller_username, u.profile_image as seller_avatar, u.username_slug AS user_slug,
               u.email as seller_email, u.user_id as seller_id
        FROM products p
        JOIN users u ON p.seller_user_id = u.user_id
        WHERE p.product_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: ' . BASE_URL . '/pages/shop.php');
    exit();
}

// Eladó statisztikák
$sql = "SELECT 
            u.avg_rating,
            COUNT(p.product_id) AS total_products
        FROM users u
        LEFT JOIN products p 
            ON p.seller_user_id = u.user_id
            AND p.product_status = 'active'
        WHERE u.user_id = ?
        GROUP BY u.user_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product['seller_id']);
$stmt->execute();
$result_stats = $stmt->get_result();
$seller_stats = $result_stats->fetch_assoc();

// Értékek feldolgozása
$total_products = (int)$seller_stats['total_products'];

$avg_rating = $seller_stats['avg_rating'] !== null
    ? number_format((float)$seller_stats['avg_rating'], 1)
    : '0.0';


// Termék képek lekérése - sorrend a feltöltés szerint (vagy is_primary szerint)
$sql = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, image_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$images_result = $stmt->get_result();
$images = $images_result->fetch_all(MYSQLI_ASSOC);

// Ha nincs kép, alapértelmezettet használunk
if (empty($images)) {
    // Csak a relatív útvonalat adjuk meg, perjel nélkül az elején
    $images = [['image_path' => 'uploads/products/default_product.png']];
}

// Hasonló termékek lekérése
$sql = "SELECT p.*, u.username as seller_username,
               (SELECT image_path FROM product_images WHERE product_id = p.product_id ORDER BY is_primary DESC LIMIT 1) as main_image
        FROM products p
        JOIN users u ON p.seller_user_id = u.user_id
        WHERE p.category = ? 
          AND p.product_id != ?           -- Kizárjuk az aktuális terméket
          AND p.product_status = 'active'
        ORDER BY p.created_at DESC
        LIMIT 4";

$stmt = $conn->prepare($sql);
// Csak a kategóriát és az aktuális ID-t adjuk át
$stmt->bind_param('si', $product['category'], $product_id); 
$stmt->execute();
$similar_result = $stmt->get_result();
$similar_products = $similar_result->fetch_all(MYSQLI_ASSOC);