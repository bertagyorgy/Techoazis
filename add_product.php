<?php
// add_product.php
session_start();
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/app/db.php';


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
        $insert_sql = "INSERT INTO products (
                            product_name, 
                            category, 
                            price, 
                            pickup_location, 
                            product_status, 
                            product_description, 
                            seller_user_id,
                            created_at,
                            updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('ssisssi', 
            $product_name, $category, $price, $pickup_location, 
            $product_status, $description, $user_id
        );

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            // Sikeres feltöltés után átirányíthatjuk a termék adatlapjára vagy maradhatunk itt
            header("Location: " . BASE_URL . "/product_detail.php?id=$new_id&msg=success");
            exit();
        } else {
            $error_msg = "Hiba történt a mentés során: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | Új termék feltöltése</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/container&grid_system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/static/index.js" defer></script>

    <style>
        .edit-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark); }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 1rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        textarea.form-control { min-height: 150px; resize: vertical; }
        .btn-submit-style {
            background: var(--primary-500, #2563eb);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit-style:hover {
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
                <h2><i class="fas fa-plus-circle"></i> Új termék hirdetése</h2>
                <a href="<?= BASE_URL ?>/shop.php" class="btn-back" style="text-decoration: none; color: var(--text-muted);">
                    <i class="fas fa-arrow-left"></i> Vissza a shopba
                </a>
            </div>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/add_product.php" method="POST">
                <div class="form-group">
                    <label for="product_name">Termék neve</label>
                    <input type="text" id="product_name" name="product_name" class="form-control" 
                           placeholder="Pl. Nvidia RTX 3080 Videókártya" required>
                </div>

                <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="category">Kategória</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="" disabled selected>Válassz kategóriát...</option>
                            <option value="Hardver">Hardver</option>
                            <option value="Periféria">Periféria</option>
                            <option value="Laptop">Laptop</option>
                            <option value="Egyéb">Egyéb</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Ár (Ft)</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               placeholder="0" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pickup_location">Átvétel helye</label>
                    <input type="text" id="pickup_location" name="pickup_location" class="form-control" 
                           placeholder="Pl. Budapest, XI. kerület">
                </div>

                <div class="form-group">
                    <label for="product_description">Részletes leírás</label>
                    <textarea id="product_description" name="product_description" class="form-control" 
                              placeholder="Írd le a termék állapotát, garanciát, stb..."></textarea>
                </div>

                <div style="margin-top: 2rem; border-top: 1px solid #eee; pt-2rem; padding-top: 1.5rem;">
                    <button type="submit" class="btn-submit-style">
                        <i class="fas fa-upload"></i>
                        Termék feltöltése
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>