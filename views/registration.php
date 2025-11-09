<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. URL ÉS ÚTVONAL DEFINÍCIÓK
// A teljes URL a JS átirányításhoz és az email linkhez.
$base_url = 'http://localhost/sulisprojektek/Techoazis/'; 
// Relatív gyökér útvonal a navigációs linkekhez (pl. CSS, JS, login.php-ra mutató link)
$root_path = '/sulisprojektek/Techoazis/'; 


// 2. FÁJL BETÖLTÉSEK JAVÍTÁSA: ../ a views mappából
// db.php: views/ -> app/db.php
include __DIR__ . '/../app/db.php'; 

// PHPMailer komponensek importálása
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// vendor/autoload.php: views/ -> vendor/autoload.php
require __DIR__ . '/../vendor/autoload.php';

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
        $activation_code = bin2hex(random_bytes(16)); // Aktivációs kód generálása
        
        $sql = "INSERT INTO users (username, email, user_password, is_active, registration_date, user_role, ip, activation_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_active = 'P'; // Pending (Függőben)
            $registration_date = date("Y-m-d H:i:s");
            $user_role = 'F';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $stmt->bind_param(
                "ssssssss",
                $username,
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
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; 
                    $mail->SMTPAuth = true;
                    $mail->Username = 'sendergmail.com'; // A TE email címed
                    $mail->Password = 'password'; // A TE Gmail App Password-öd
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('sender@gmail.com', 'Techoazis Registration');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true); 
                    
                    // Aktivációs link létrehozása: a $base_url-t használjuk
                    $activation_link = $base_url . "views/activate.php?email=" . urlencode($email) . "&code=" . urlencode($activation_code);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Aktiváld a Techoazis fiókodat!';
                    $mail->Body    = '
                        <h2>Köszönjük a regisztrációt!</h2>
                        <p>Kérlek, kattints az alábbi linkre a fiókod aktiválásához:</p>
                        <p><a href="' . $activation_link . '" style="color: blue;">Fiók aktiválása</a></p>
                        <p>Ha nem te regisztráltál, hagyd figyelmen kívül ezt az emailt.</p>';

                    $mail->send();
                    
                    // Átirányítás session üzenettel a login.php-ra (a teljes $base_url-t használva)
                    $_SESSION['registration_message'] = "Sikeres regisztráció! Kérlek, ellenőrizd az email címedet (beleértve a spam mappát) a fiók aktiválásához.";
                    echo "<script>window.location.href='{$base_url}views/login.php';</script>";
                    exit();

                } catch (Exception $e) {
                    $errors[] = "A regisztráció sikeres volt, de hiba történt az aktiváló email küldésekor. Kérlek, vedd fel velünk a kapcsolatot. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $errors[] = "Hiba a mentés során. Kérlek, próbáld újra később. MySQL Hiba: " . $conn->error;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="<?= $root_path ?>images/palmtree_favicon.svg"> 
    <script src="<?= $root_path ?>static/index.js" defer></script> 
    <title>Techoazis | Registration</title>
    <link rel="stylesheet" href="<?= $root_path ?>static/index.css"> 
</head>
<body>
<?php 
// navbar.php a views mappában van
include __DIR__ . '/navbar.php'; 
?>
    <div class="background">
        <div class="login-container">
            <section class="login-box">
                <h2>Regisztráció</h2>
                <?php if (!empty($error_message)) : ?>
                    <div class="login-alert"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="login-form-group">
                        <label for="username" class="login-label">Felhasználónév</label>
                        <input type="text" name="username" id="username" class="login-input" required>
                    </div>
                    <div class="login-form-group">
                        <label for="email" class="login-label">Email</label>
                        <input type="email" name="email" id="email" class="login-input" required>
                    </div>
                    <div class="login-form-group">
                        <label for="password" class="login-label">Jelszó</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="login-input" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                    <div class="login-form-group">
                        <label for="confirm_password" class="login-label">Jelszó megerősítése</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="login-input" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                    <button type="submit" name="submit" class="login-button">Regisztráció</button>
                </form>

                <p class="login-footer">Van már fiókod? <a href="<?= $root_path ?>views/login.php">Bejelentkezés</a></p>
            </section>
        </div>
    </div>
</body>
</html>