<?php
// Hibakeresés bekapcsolása, hogy lásd ha mégis baj van
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';

// 1. Jogosultság ellenőrzés
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

$viewer_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_role FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $viewer_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res || $res['user_role'] === 'F') {
    die("Hiba: Nincs jogosultságod.");
}

// 2. Slug generáló függvény (ékezetmentesítés és kisbetűsítés)
function generateSlug($text) {
    $text = str_replace(['á','é','í','ó','ö','ő','ú','ü','ű','Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'], ['a','e','i','o','o','o','u','u','u','a','e','i','o','o','o','u','u','u'], $text);
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return $text . '-' . time(); // Egyedivé tétel időbélyeggel
}

// 3. Feldolgozás
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $summary = trim($_POST['summary']);
    $content = trim($_POST['content']);
    $category_id = (int)$_POST['category_id'];
    $reading_minutes = (int)$_POST['reading_minutes'];
    
    // Generáljuk le a hiányzó slug-ot!
    $slug = generateSlug($title);
    
    $cover_path = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $upload_dir = 'uploads/articles/';
        if (!is_dir(ROOT_PATH . '/' . $upload_dir)) mkdir(ROOT_PATH . '/' . $upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = "art_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], ROOT_PATH . '/' . $upload_dir . $filename)) {
            $cover_path = $upload_dir . $filename;
        }
    }

    // SQL javítása: bekerült a 'slug' oszlop is
    $sql = "INSERT INTO articles 
            (category_id, author_user_id, title, slug, summary, content, cover_image, reading_minutes, article_status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW())";
    
    $stmt = $conn->prepare($sql);
    
    // Típusok: i (int), i (int), s (string), s (string), s (string), s (string), s (string), i (int)
    $stmt->bind_param("iisssssi", 
        $category_id, 
        $viewer_id, 
        $title, 
        $slug, 
        $summary, 
        $content, 
        $cover_path, 
        $reading_minutes
    );

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "/pages/articles.php?upload=success");
        exit();
    } else {
        die("MySQL hiba: " . $stmt->error);
    }
}