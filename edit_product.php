<?php
// edit_product.php
session_start();
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/app/db.php';

// Csak bejelentkezett felhasználók szerkeszthetnek
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$product_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// 1. Termék adatok lekérése és jogosultság ellenőrzése
$sql = "SELECT * FROM products WHERE product_id = ? AND seller_user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Ha a termék nem létezik, vagy nem a felhasználóé
if (!$product) {
    header("Location: " . BASE_URL . "/shop.php");
    exit();
}

$success_msg = "";
$error_msg = "";

// 2. Form beküldés kezelése (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = (int)$_POST['price'] ?? 0;
    $pickup_location = $_POST['pickup_location'] ?? '';
    $product_status = $_POST['product_status'] ?? 'active';
    $description = $_POST['product_description'] ?? '';

    // Egyszerű validálás
    if (empty($product_name) || empty($category)) {
        $error_msg = "A név és a kategória megadása kötelező!";
    } else {
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
            $success_msg = "Termék sikeresen frissítve!";
            // Frissítjük a lokális $product változót is a megjelenítéshez
            $product['product_name'] = $product_name;
            $product['category'] = $category;
            $product['price'] = $price;
            $product['pickup_location'] = $pickup_location;
            $product['product_status'] = $product_status;
            $product['product_description'] = $description;
        } else {
            $error_msg = "Hiba történt a mentés során.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termék szerkesztése - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/container&grid_system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="<?= BASE_URL ?>/static/index.js" defer></script>
    <script src="<?= BASE_URL ?>/static/forum.js" defer></script>

    <style>
        .edit-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark); }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 1rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        textarea.form-control { min-height: 150px; resize: vertical; }
        .btn-message-seller {
            background: var(--primary-500, #2563eb);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-message-seller:hover:not(.disabled) {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include ROOT_PATH . '/views/navbar.php'; ?>

    <div class="container section-padding">
        <div class="edit-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2><i class="fas fa-edit"></i> Termék szerkesztése</h2>
                <a href="<?= BASE_URL ?>/product_detail.php?id=<?php echo $product_id; ?>" class="btn-back">
                    <i class="fas fa-eye"></i> Megtekintés
                </a>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="product_name">Termék neve</label>
                    <input type="text" id="product_name" name="product_name" class="form-control" 
                           value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>

                <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="category">Kategória</label>
                        <select id="category" name="category" class="form-control">
                            <option value="Hardver" <?php echo $product['category'] == 'Hardver' ? 'selected' : ''; ?>>Hardver</option>
                            <option value="Periféria" <?php echo $product['category'] == 'Periféria' ? 'selected' : ''; ?>>Periféria</option>
                            <option value="Laptop" <?php echo $product['category'] == 'Laptop' ? 'selected' : ''; ?>>Laptop</option>
                            <option value="Egyéb" <?php echo $product['category'] == 'Egyéb' ? 'selected' : ''; ?>>Egyéb</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Ár (Ft)</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               value="<?php echo htmlspecialchars($product['price']); ?>">
                    </div>
                </div>

                <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="pickup_location">Átvétel helye</label>
                        <input type="text" id="pickup_location" name="pickup_location" class="form-control" 
                               value="<?php echo htmlspecialchars($product['pickup_location']); ?>" placeholder="Város, kerület...">
                    </div>
                    <div class="form-group">
                        <label for="product_status">Állapot</label>
                        <select id="product_status" name="product_status" class="form-control">
                            <option value="active" <?php echo $product['product_status'] == 'active' ? 'selected' : ''; ?>>Aktív (Listázva)</option>
                            <option value="sold" <?php echo $product['product_status'] == 'sold' ? 'selected' : ''; ?>>Eladva</option>
                            <option value="hidden" <?php echo $product['product_status'] == 'hidden' ? 'selected' : ''; ?>>Rejtett</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="product_description">Leírás</label>
                    <textarea id="product_description" name="product_description" class="form-control"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn-message-seller btn-submit-style" style="background: var(--primary-500);">
                        <i class="fas fa-save"></i>
                        Változtatások mentése
                    </button>
                    
                </div>
            </form>
        </div>
    </div>
</body>
</html>