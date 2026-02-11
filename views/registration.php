<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. URL ÉS ÚTVONAL DEFINÍCIÓK
// A teljes URL a JS átirányításhoz és az email linkhez.



// 2. FÁJL BETÖLTÉSEK JAVÍTÁSA: ../ a views mappából
// db.php: views/ -> app/db.php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/app/helpers.php';
require_once ROOT_PATH . '/envreader.php';

// PHPMailer komponensek importálása
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// vendor/autoload.php: views/ -> vendor/autoload.php
require_once ROOT_PATH . '/vendor/autoload.php';

$_SESSION['registration_message'] = "";
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $errors = [];

    // alapadatok
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validációk (A kód többi része itt marad)
    if ($username === '') { $errors[] = "Kérlek, add meg a felhasználónevet."; } elseif (strlen($username) < 3) { $errors[] = "A felhasználónév legyen legalább 3 karakter."; }
    if ($email === '') { $errors[] = "Kérlek, add meg az e-mail címed."; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Érvénytelen e-mail cím formátum."; }
    if ($password === '') { $errors[] = "Kérlek, add meg a jelszót."; } elseif (strlen($password) < 6) { $errors[] = "A jelszó legyen legalább 6 karakter."; }
    if ($confirm_password === '') { $errors[] = "Kérlek, erősítsd meg a jelszót."; } elseif ($password !== $confirm_password) { $errors[] = "A jelszavak nem egyeznek."; }

    // Adatbázis ellenőrzés (username/email foglalt-e)
    if (empty($errors)) {
        $sql = "SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $email);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = "Ez a felhasználónév vagy e-mail már foglalt.";
                }
            } else { $errors[] = "Adatbázis hiba: nem sikerült ellenőrizni a meglévő felhasználókat."; }
            $stmt->close();
        } else { $errors[] = "Adatbázis hiba: előkészítés nem sikerült."; }
    }

    // Ha nincs hiba: beszúrás és email küldés
    if (empty($errors)) {
        // USERNAME SLUG (converter)
        $base_slug = make_slug($username);

        // védelem: ha valamiért üres slug lenne (pl. csak szimbólumok), kap fallbacket
        if ($base_slug === '') {
            $base_slug = 'user';
        }

        // regisztrációnál még nincs user_id, ezért -1-et adunk
        $username_slug = unique_slug($conn, $base_slug, -1);

        $activation_code = bin2hex(random_bytes(16)); // Aktivációs kód generálása
        
        $sql = "INSERT INTO users (username, username_slug, email, user_password, is_active, registration_date, user_role, ip, activation_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_active = 'P'; // Pending (Függőben)
            $registration_date = date("Y-m-d H:i:s");
            $user_role = 'F';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $stmt->bind_param(
                "sssssssss",
                $username,
                $username_slug,
                $email,
                $hashed_password,
                $is_active,
                $registration_date,
                $user_role,
                $ip,
                $activation_code
            );

            if ($stmt->execute()) {
                // Sikeres adatbázis mentés, most küldjük az emailt
                $mail = new PHPMailer(true);
                
                try {
                    // SMTP BEÁLLÍTÁSOK (HASZNÁLD A SAJÁT ADATAIDAT)
                    loadEnv(); // vagy loadEnv(__DIR__.'/.env');
                    

                    $mail->isSMTP();
                    $mail->Host = getenv('SMTP_HOST'); 
                    $mail->SMTPAuth = true;
                    $mail->Username = getenv('SMTP_VER_EMAIL'); // A TE email címed
                    $mail->Password = getenv('SMTP_VER_EMAIL_PASSWORD'); // A TE Gmail App Password-öd
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = getenv('SMTP_PORT');

                    $mail->setFrom(getenv('SMTP_VER_EMAIL'), 'Techoazis Registration');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true); 
                    
                    // Aktivációs link létrehozása: a BASE_URL-t használjuk
                    $activation_link = BASE_URL . "/views/activate.php?email=" . urlencode($email) . "&code=" . urlencode($activation_code);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Aktiváld a Techoázis fiókodat!';
                    $mail->Body    = '
                        <div style="font-family: Arial, sans-serif;">
                            <h1>Köszönjük a regisztrációt!</h1>
                            <p>Kérlek, kattints az alábbi linkre a fiókod aktiválásához:</p>
                            <p><a href="' . $activation_link . '" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Fiók aktiválása</a></p>
                            <p>Ha nem te regisztráltál, hagyd figyelmen kívül ezt az emailt.</p>
                        </div>';

                    $mail->send();
                    
                    // Átirányítás session üzenettel a login.php-ra (a teljes BASE_URL-t használva)
                    $_SESSION['registration_message'] = "Sikeres regisztráció! Kérlek, ellenőrizd az email címedet (beleértve a spam mappát) a fiók aktiválásához.";
                    echo "<script>window.location.href='" . BASE_URL . "/views/login.php';</script>";
                    exit();

                } catch (Exception $e) {
                    $errors[] = "A regisztráció sikeres volt, de hiba történt az aktiváló email küldésekor. Kérlek, vedd fel velünk a kapcsolatot.";
                }
            } else {
                $errors[] = "Hiba a mentés során. Kérlek, próbáld újra később.";
            }

            $stmt->close();
        } else {
            $errors[] = "Adatbázis hiba: nem sikerült előkészíteni az insert lekérdezést.";
        }
    }

    if (!empty($errors)) {
        $error_message = implode("<br>", array_map('htmlspecialchars', $errors));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg"> 
    <title>Techoazis | Registration</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css"> 
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login_page.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
</head>
<body>
<?php 
// navbar.php a views mappában van
include ROOT_PATH . '/views/navbar.php';
?>
    <div class="background">
        <div class="login-container">
            <section class="login-box">
                <h2>Regisztráció</h2>
                <?php if (!empty($error_message)) : ?>
                    <div class="login-alert"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($_SESSION['registration_message'])) : ?>
                    <div class="login-success"><?php echo $_SESSION['registration_message']; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="login-form-group">
                        <label for="username" class="login-label">Felhasználónév</label>
                        <input type="text" name="username" id="username" class="login-input" placeholder="Felhasználónév" required>
                    </div>
                    <div class="login-form-group">
                        <label for="email" class="login-label">Email (erre küldjük az aktiváló linket)</label>
                        <input type="email" name="email" id="email" class="login-input" placeholder="Email" required>
                    </div>
                    <div class="login-form-group">
                        <label for="password" class="login-label">Jelszó</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="login-input" placeholder="Jelszó" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                    <div class="login-form-group">
                        <label for="confirm_password" class="login-label">Jelszó megerősítése</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="login-input" placeholder="Jelszó újra" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                    <button type="submit" name="submit" class="login-button">Regisztráció</button>
                </form>
                <p class="login-separator">───── Van már fiókod? ─────</p>
                <a href="<?= BASE_URL ?>/views/login.php"><button class="registration-button">Bejelentkezés</button></a>
            </section>
        </div>
    </div>
</body>
</html>