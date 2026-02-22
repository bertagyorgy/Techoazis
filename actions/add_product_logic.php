<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Config betöltése
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

// --- TINIFY ÉS KÖRNYEZETI VÁLTOZÓK BEÁLLÍTÁSA ---
require_once ROOT_PATH . '/core/envreader.php';
loadEnv();

// Az image_optimizer.php behívása a központi helyről
require_once ROOT_PATH . '/actions/image_optimizer.php';

// Csak bejelentkezett felhasználók tölthetnek fel terméket
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Form beküldés kezelése (Insert)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = (int)($_POST['price'] ?? 0);
    $pickup_location = $_POST['pickup_location'] ?? '';
    $product_status = 'active'; // Új terméknél alapértelmezett
    $description = $_POST['product_description'] ?? '';

    // Egyszerű validálás
    if (empty($product_name) || empty($category) || $price <= 0) {
        $error_msg = "A név, kategória és egy érvényes ár megadása kötelező!";
    } else {
        // --- KÉPEK ELŐZETES ELLENŐRZÉSE ÉS GYŰJTÉSE ---
        $max_file_size = 5 * 1024 * 1024; // 5 MB
        $valid_images = [];

        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    if ($_FILES['images']['size'][$key] > $max_file_size) {
                        $error_msg = "Az egyik feltöltött kép túl nagy! A maximális méret képenként 5MB.";
                        break;
                    }
                    $valid_images[] = [
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'name' => $name
                    ];
                }
            }
        }

        if (empty($error_msg)) {
            // 1. Termék mentése
            $insert_sql = "INSERT INTO products (product_name, category, price, pickup_location, product_status, product_description, seller_user_id, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param('ssisssi', $product_name, $category, $price, $pickup_location, $product_status, $description, $user_id);

            if ($stmt->execute()) {
                $product_id = $conn->insert_id; 
                
                if (!empty($valid_images)) {
                    $upload_dir = ROOT_PATH . '/uploads/products/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                    $img_sql = "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)";
                    $img_stmt = $conn->prepare($img_sql);

                    foreach ($valid_images as $index => $img_data) {
                        $file_ext = strtolower(pathinfo($img_data['name'], PATHINFO_EXTENSION));
                        $file_name = time() . '_' . $index . '_' . uniqid() . '.' . $file_ext;
                        $target_file = $upload_dir . $file_name;
                        $db_path = 'uploads/products/' . $file_name;

                        if (move_uploaded_file($img_data['tmp_name'], $target_file)) {
                            // --- KÉP OPTIMALIZÁLÁSA (Tinify hívása) ---
                            optimizeImageWithTinify($target_file, 'product');

                            // Az abszolút első sikeresen feltöltött kép a borítókép
                            $is_primary = ($index === 0) ? 1 : 0;
                            $sort_order = $index + 1;

                            $img_stmt->bind_param('isii', $product_id, $db_path, $is_primary, $sort_order);
                            $img_stmt->execute();
                        }
                    }
                    $img_stmt->close();
                }

                header("Location: " . BASE_URL . "/pages/product_detail.php?id=$product_id&msg=success");
                exit(); 
            } else {
                $error_msg = "Hiba: " . $conn->error;
            }
        }
    }
}