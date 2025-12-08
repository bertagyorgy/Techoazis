<?php
session_start();
include "./app/db.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("Hozzáférés megtagadva.");
}

$user_id = $_SESSION['user_id'];
$group_id = intval($_POST['group_id']);
$title = trim($_POST['title']);
$content = trim($_POST['content']);

if ($title == "" || $content == "") {
    die("Hiányzó adatok.");
}

// POSZT LÉTREHOZÁSA
$stmt = $conn->prepare("INSERT INTO posts (user_id, group_id, title, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $user_id, $group_id, $title, $content);
$stmt->execute();

$post_id = $stmt->insert_id;
$stmt->close();

// === KÉPFELTÖLTÉS ===
$upload_dir = "uploads/posts/";
$max_images = 3;
$max_size = 5 * 1024 * 1024; // 5MB

$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!empty($_FILES['images']['name'][0])) {

    if (count($_FILES['images']['name']) > $max_images) {
        die("Maximum 3 képet tölthetsz fel.");
    }

    for ($i = 0; $i < count($_FILES['images']['name']); $i++) {

        $tmp = $_FILES['images']['tmp_name'][$i];
        $orig_name = $_FILES['images']['name'][$i];
        $size = $_FILES['images']['size'][$i];

        // --- MÉRET ELLENŐRZÉS (MAX 5MB) ---
        if ($size > $max_size) {
            die("A(z) '$orig_name' mérete meghaladja az 5MB-ot.");
        }

        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

        // --- TÍPUS ELLENŐRZÉS ---
        if (!in_array($ext, $allowed_ext)) {
            die("A(z) '$orig_name' fájltípus nem engedélyezett. Csak JPG, PNG, GIF, WEBP!");
        }

        // új fájlnév (biztonságos, egyedi)
        $new_name = $post_id . "_" . time() . "_" . rand(1000,9999) . "." . $ext;
        $full_path = $upload_dir . $new_name;

        // duplikáció ellenőrzés
        if (file_exists($full_path)) {
            die("Hiba: duplikált fájlnév történt. Próbáld újra!");
        }

        // feltöltés
        if (move_uploaded_file($tmp, $full_path)) {

            // adatbázis mentés
            $stmt_img = $conn->prepare("INSERT INTO images (post_id, image_path) VALUES (?, ?)");
            $stmt_img->bind_param("is", $post_id, $full_path);
            $stmt_img->execute();
            $stmt_img->close();

        } else {
            die("A(z) '$orig_name' feltöltése sikertelen.");
        }
    }
}


$conn->close();

// Siker → vissza az adott csoportba
echo "<script>window.location.href='../forum_group.php?group={$group_id}';</script>";
exit();
?>
