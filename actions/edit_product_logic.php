<?php
// edit_product_backend.php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Config és adatbázis betöltése
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

// --- TINIFY ÉS KÖRNYEZETI VÁLTOZÓK BEÁLLÍTÁSA ---
require_once ROOT_PATH . '/core/envreader.php';
loadEnv();
// Az image_optimizer.php behívása a központi helyről
require_once ROOT_PATH . '/actions/image_optimizer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$product_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Termék adatainak lekérése
$sql = "SELECT * FROM products WHERE product_id = ? AND seller_user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: " . BASE_URL . "/pages/shop.php");
    exit();
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = (int)($_POST['price'] ?? 0);
    $pickup_location = $_POST['pickup_location'] ?? '';
    $product_status = $_POST['product_status'] ?? 'active';
    $description = $_POST['product_description'] ?? '';

    if (empty($product_name) || empty($category)) {
        $error_msg = "A név és a kategória megadása kötelező!";
    } else {
        // --- ÚJ KÉPEK ELŐZETES ELLENŐRZÉSE (MÉRET LIMIT 5MB) ---
        $max_file_size = 5 * 1024 * 1024; // 5 MB
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['size'] as $size) {
                if ($size > $max_file_size) {
                    $error_msg = "Az egyik feltöltött kép túl nagy! A maximális méret képenként 5MB.";
                    break;
                }
            }
        }

        if (empty($error_msg)) {
            $update_sql = "UPDATE products SET 
                            product_name = ?, 
                            category = ?, 
                            price = ?, 
                            pickup_location = ?, 
                            product_status = ?, 
                            product_description = ?, 
                            updated_at = NOW() 
                           WHERE product_id = ? AND seller_user_id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ssisssii', 
                $product_name, $category, $price, $pickup_location, 
                $product_status, $description, $product_id, $user_id
            );

            if ($update_stmt->execute()) {
                // Meglévő képek törlése, ha kérték
                if (!empty($_POST['removed_images'])) {
                    foreach ($_POST['removed_images'] as $img_id) {
                        $img_id = (int)$img_id;
                        $path_sql = "SELECT image_path FROM product_images WHERE image_id = ? AND product_id = ?";
                        $p_stmt = $conn->prepare($path_sql);
                        $p_stmt->bind_param('ii', $img_id, $product_id);
                        $p_stmt->execute();
                        $p_res = $p_stmt->get_result();
                        
                        if ($row = $p_res->fetch_assoc()) {
                            $full_path = ROOT_PATH . '/' . $row['image_path'];
                            if (file_exists($full_path)) {
                                unlink($full_path);
                            }
                        }
                        $del_sql = "DELETE FROM product_images WHERE image_id = ? AND product_id = ?";
                        $d_stmt = $conn->prepare($del_sql);
                        $d_stmt->bind_param('ii', $img_id, $product_id);
                        $d_stmt->execute();
                    }
                }

                // Új képek feltöltése és optimalizálása
                if (!empty($_FILES['images']['name'][0])) {
                    $upload_dir = ROOT_PATH . '/uploads/products/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                    $count_sql = "SELECT COUNT(*) as total FROM product_images WHERE product_id = ?";
                    $c_stmt = $conn->prepare($count_sql);
                    $c_stmt->bind_param('i', $product_id);
                    $c_stmt->execute();
                    $current_count = $c_stmt->get_result()->fetch_assoc()['total'];

                    $img_sql = "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)";
                    $img_stmt = $conn->prepare($img_sql);

                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                            $file_ext = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
                            $file_name = time() . '_' . $key . '_' . uniqid() . '.' . $file_ext;
                            $target_file = $upload_dir . $file_name;
                            $db_path = 'uploads/products/' . $file_name;

                            if (move_uploaded_file($tmp_name, $target_file)) {
                                // --- KÉP OPTIMALIZÁLÁSA (Tinify hívása) ---
                                optimizeImageWithTinify($target_file, 'product');

                                $is_primary = ($current_count == 0 && $key === 0) ? 1 : 0;
                                $sort_order = $current_count + $key + 1;
                                $img_stmt->bind_param('isii', $product_id, $db_path, $is_primary, $sort_order);
                                $img_stmt->execute();
                            }
                        }
                    }
                    if(isset($img_stmt)) $img_stmt->close();
                }
                $success_msg = "Termék és képek sikeresen frissítve!";
                
                // Frissített adatok visszaírása a változóba a megjelenítéshez
                $product['product_name'] = $product_name;
                $product['category'] = $category;
                $product['price'] = $price;
                $product['pickup_location'] = $pickup_location;
                $product['product_status'] = $product_status;
                $product['product_description'] = $description;
            } else {
                $error_msg = "Hiba történt a mentés során: " . $conn->error;
            }
        }
    }
}

// Jelenlegi képek számának lekérése a frontend validációjához
$count_query = "SELECT COUNT(*) as total FROM product_images WHERE product_id = ?";
$c_stmt = $conn->prepare($count_query);
$c_stmt->bind_param('i', $product_id);
$c_stmt->execute();
$current_image_count = $c_stmt->get_result()->fetch_assoc()['total'];