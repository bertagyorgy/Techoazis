<?php
session_start();
include 'db.php';

// Ellenőrizzük, hogy be van-e jelentkezve a felhasználó
if (!isset($_SESSION['username'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$username = $_SESSION['username'];

// Lekérjük a user adatait
$query = "SELECT user_id, username, email, registration_date, user_role, profile_image FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Alapértelmezett kép, ha nincs megadva
$profile_image = !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'images/anonymous.png';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoazis | Profile</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="profile-section">
    <div class="profile-container">
        <div class="profile-left">
            <img src="<?php echo $profile_image; ?>" alt="Profilkép" class="profile-image">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p class="profile-role">
                <?php 
                    if ($user['user_role'] === 'A') echo 'Adminisztrátor';
                    elseif ($user['user_role'] === 'F') echo 'Felhasználó';
                    else echo 'Ismeretlen szerep';
                ?>
            </p>

            <button class="profile-btn profile-change-image"><i class="fa-solid fa-image"></i> Profilkép módosítása</button>
            <button class="profile-btn profile-edit"><i class="fa-solid fa-pen"></i> Adatok szerkesztése</button>
        </div>

        <div class="profile-right">
            <h3>Felhasználói adatok</h3>
            <div class="profile-info-grid">
                <p><strong>Felhasználónév:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>E-mail cím:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Regisztráció dátuma:</strong> <?php echo htmlspecialchars($user['registration_date']); ?></p>
                <p><strong>Azonosító (ID):</strong> <?php echo htmlspecialchars($user['user_id']); ?></p>
            </div>

            <div class="profile-actions">
                <button class="profile-btn logout"><i class="fa-solid fa-right-from-bracket"></i> Kijelentkezés</button>
                <button class="profile-btn delete"><i class="fa-solid fa-trash"></i> Fiók törlése</button>
            </div>
        </div>
    </div>
</section>

</body>
</html>
