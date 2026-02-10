<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Config és DB betöltése a konzisztencia miatt
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/app/profile_stats.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("Hozzáférés megtagadva.");
}

$user_id = $_SESSION['user_id'];
$group_id = intval($_POST['group_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if ($title === "" || $content === "") { 
    $_SESSION['error'] = "Minden mezőt tölts ki!"; 
    header("Location: " . BASE_URL . "/forum_group.php?group={$group_id}"); 
    exit; 
}

// === TRANZAKCIÓ INDÍTÁSA VAGY BIZTONSÁGI SORREND ===
// Előbb ellenőrizzük a fájlokat, mielőtt bármit írnánk a DB-be
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
$max_size = 5 * 1024 * 1024;
$upload_dir = ROOT_PATH . "/uploads/posts/"; // Abszolút útvonal!

// Ha nem létezik a mappa, próbáljuk létrehozni
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// POSZT LÉTREHOZÁSA
$stmt = $conn->prepare("INSERT INTO posts (user_id, group_id, title, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $user_id, $group_id, $title, $content);

if ($stmt->execute()) {
    $post_id = $stmt->insert_id;
    $stmt->close();

    // === KÉPFELTÖLTÉS ===
    if (!empty($_FILES['images']['name'][0])) {
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            $tmp = $_FILES['images']['tmp_name'][$i];
            $orig_name = $_FILES['images']['name'][$i];
            $size = $_FILES['images']['size'][$i];
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed_ext) && $size <= $max_size) {
                $new_name = $post_id . "_" . time() . "_" . rand(1000,9999) . "." . $ext;
                $destination = $upload_dir . $new_name;
                // Az adatbázisba relatív utat mentünk (pl. uploads/posts/kep.jpg)
                $db_path = "uploads/posts/" . $new_name;

                if (move_uploaded_file($tmp, $destination)) {
                    $stmt_img = $conn->prepare("INSERT INTO images (post_id, image_path) VALUES (?, ?)");
                    $stmt_img->bind_param("is", $post_id, $db_path);
                    $stmt_img->execute();
                    $stmt_img->close();
                }
            }
        }
    }
    
    $_SESSION['success'] = "Poszt sikeresen létrehozva!";
    refreshUserStats($conn, $user_id);
} else {
    $_SESSION['error'] = "Hiba történt a mentés során.";
}

$conn->close();

// Siker/Hiba után visszairányítás PHP-ból (nem JS-el, hogy elkerüljük a dupla küldést)
header("Location: " . BASE_URL . "/forum_group.php?group={$group_id}");
exit();
?>