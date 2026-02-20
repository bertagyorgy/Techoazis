<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Config betöltése (ez definiálja a ROOT_PATH-ot és a BASE_URL-t)
require_once __DIR__ . '/../core/config.php';

// 2. Adatbázis betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/app/profile_stats.php';

$profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($profile_user_id > 0) {
    refreshUserStats($conn, $profile_user_id);
}

// 3. Biztonsági ellenőrzés javítása
if (!isset($_SESSION['username'])) {
    // PHP alapú átirányítás a BASE_URL használatával
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$viewer_id = (int)($_SESSION['user_id'] ?? 0);
if ($viewer_id <= 0) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

// slug param (pl. profile?u=admin)
$slug = isset($_GET['u']) ? trim($_GET['u']) : null;

// 1) Nézett user lekérése slug alapján (ha van)
if ($slug !== null && $slug !== '') {
    $stmt = $conn->prepare("
        SELECT user_id, username, username_slug, email, registration_date, user_role, profile_image,
               total_posts, total_comments, sold_items, bought_items, avg_rating 
        FROM users 
        WHERE username_slug = ? 
        LIMIT 1
    ");
    $stmt->bind_param("s", $slug);
} else {
    // 2) Ha nincs slug -> saját profil
    $stmt = $conn->prepare("
        SELECT user_id, username, username_slug, email, registration_date, user_role, profile_image,
               total_posts, total_comments, sold_items, bought_items, avg_rating 
        FROM users 
        WHERE user_id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $viewer_id);
}

$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    exit("Nincs ilyen profil.");
}

$is_owner = ((int)$user['user_id'] === $viewer_id);
$profile_user_id = (int)$user['user_id'];

// Aktív termékek száma (nézett profilhoz!)
$stmt = $conn->prepare("SELECT COUNT(*) as active_count FROM products WHERE seller_user_id = ? AND product_status = 'active'");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$active_products = (int)($stmt->get_result()->fetch_assoc()['active_count'] ?? 0);
$stmt->close();

// Eladott termékek száma (nézett profilhoz!)
$stmt = $conn->prepare("SELECT COUNT(*) as sold_count FROM products WHERE seller_user_id = ? AND product_status = 'sold'");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$sold_products = (int)($stmt->get_result()->fetch_assoc()['sold_count'] ?? 0);
$stmt->close();

// Feltett termékek (max 6) (nézett profilhoz!)
$stmt = $conn->prepare("
    SELECT product_id, product_name, category, price, product_status, 
           created_at, (SELECT image_path FROM product_images WHERE product_id = products.product_id AND is_primary = 1 LIMIT 1) as image_path
    FROM products 
    WHERE seller_user_id = ?
    ORDER BY created_at DESC 
    LIMIT 6
");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$products_result = $stmt->get_result();
$user_products = [];
while ($row = $products_result->fetch_assoc()) {
    if (!empty($row['image_path'])) {
        $row['image_path'] = BASE_URL . '/' . $row['image_path'];
    } else {
        $row['image_path'] = BASE_URL . '/uploads/products/default_product.png';
    }    
    $user_products[] = $row;
}
$stmt->close();

// Értékelések (nézett profilhoz!)
$stmt = $conn->prepare("
    SELECT r.*, u.username as reviewer_name
    FROM reviews r
    JOIN users u ON r.buyer_user_id = u.user_id
    WHERE r.seller_user_id = ?
    ORDER BY r.review_date DESC
    LIMIT 3
");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Beszélgetések csak a saját profilnál!
$conversations = [];
if ($is_owner) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                c.conversation_id,
                c.product_id,
                p.product_name,
                u.username AS other_user,

                (SELECT COUNT(*) 
                FROM messages m
                WHERE m.conversation_id = c.conversation_id
                AND m.sender_user_id != ?
                AND m.is_read = 0
                ) AS unread_count,

                COALESCE(
                    (SELECT MAX(m2.sent_at) FROM messages m2 WHERE m2.conversation_id = c.conversation_id),
                    c.updated_at,
                    c.created_at
                ) AS last_activity

            FROM conversations c
            JOIN products p ON p.product_id = c.product_id
            JOIN users u ON (
                (c.seller_user_id = u.user_id AND c.seller_user_id != ?) OR
                (c.buyer_user_id  = u.user_id AND c.buyer_user_id  != ?)
            )
            WHERE (c.seller_user_id = ? OR c.buyer_user_id = ?)
            ORDER BY last_activity DESC
            LIMIT 5
        ");
        $stmt->bind_param("iiiii", $viewer_id, $viewer_id, $viewer_id, $viewer_id, $viewer_id);
        $stmt->execute();
        $conversations_result = $stmt->get_result();
        while ($row = $conversations_result->fetch_assoc()) {
            $conversations[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Conversation error: " . $e->getMessage());
    }
}

// Ellenőrizzük, hogy a mentett adat http-vel vagy https-el kezdődik-e (külső link)
$is_external = preg_match('/^https?:\/\//', $user['profile_image']);

if (!empty($user['profile_image'])) {
    if ($is_external) {
        // Ha külső link (DiceBear), akkor változtatás nélkül használjuk
        $profile_image = htmlspecialchars($user['profile_image']);
    } else {
        // Ha belső fájl, akkor fűzzük hozzá a BASE_URL-t
        $profile_image = BASE_URL . '/' . htmlspecialchars($user['profile_image']);
    }
} else {
    // Alapértelmezett kép, ha nincs megadva semmi
    $profile_image = BASE_URL . 'uploads/profile_images/anonymous.png';
}

$user_role_display = ($user['user_role'] ?? '') === 'A' ? 'Adminisztrátor' : 'Felhasználó';