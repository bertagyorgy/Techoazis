<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

// 1. FÁJL BETÖLTÉSEK
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/app/helpers.php';
require_once ROOT_PATH . '/core/envreader.php';
require_once ROOT_PATH . '/app/auth_check_login.php';
loadEnv(); // Környezeti változók betöltése

// PHPMailer komponensek
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once ROOT_PATH . '/core/vendor/autoload.php';

$_SESSION['registration_message'] = "";
$error_message = '';

// A beküldést a username meglétével ellenőrizzük (mivel a gomb neve kikerült)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'])) {
    $errors = [];

    // --- RECAPTCHA V3 ELLENŐRZÉS ---
    $secretKey = getenv('RECAPTCHA_SECRET_KEY') ?: $_ENV['RECAPTCHA_SECRET_KEY']; 
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($captchaResponse)) {
        $errors[] = "Biztonsági ellenőrzés hiba (hiányzó token).";
    } else {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            'secret'   => $secretKey,
            'response' => $captchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        $verify = file_get_contents($url, false, $context);
        $responseData = json_decode($verify);

        if (!$responseData->success || $responseData->score < 0.5) {
            $errors[] = "A rendszer robotot észlelt. Kérlek, próbáld újra!";
        }
    }

    // Ha a captcha rendben van, jöhet a többi validáció
    if (empty($errors)) {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        $dicebear_url = "https://api.dicebear.com/9.x/shapes/svg?seed=" . urlencode($username);

        if ($username === '') { $errors[] = "Kérlek, add meg a felhasználónevet."; } elseif (strlen($username) < 3) { $errors[] = "A felhasználónév legalább 3 karakter."; }
        if ($email === '') { $errors[] = "Kérlek, add meg az e-mail címed."; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Érvénytelen e-mail cím."; }
        if ($password === '') { $errors[] = "Kérlek, add meg a jelszót."; } elseif (strlen($password) < 6) { $errors[] = "A jelszó legalább 6 karakter."; }
        if ($password !== $confirm_password) { $errors[] = "A jelszavak nem egyeznek."; }

        // Foglaltság ellenőrzése
        if (empty($errors)) {
            $sql = "SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = "Ez a felhasználónév vagy e-mail már foglalt.";
                }
                $stmt->close();
            }
        }

        // Regisztráció végrehajtása
        if (empty($errors)) {
            $base_slug = make_slug($username) ?: 'user';
            $username_slug = unique_slug($conn, $base_slug, -1);
            $activation_code = bin2hex(random_bytes(16));
            
            $sql = "INSERT INTO users (username, username_slug, email, user_password, is_active, registration_date, user_role, ip, profile_image, activation_code)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = $conn->prepare($sql)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $is_active = 'P'; 
                $registration_date = date("Y-m-d H:i:s");
                $user_role = 'F';
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';

                $stmt->bind_param("ssssssssss", $username, $username_slug, $email, $hashed_password, $is_active, $registration_date, $user_role, $ip, $dicebear_url, $activation_code);

                if ($stmt->execute()) {
                    // Email küldés
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = getenv('SMTP_HOST'); 
                        $mail->SMTPAuth = true;
                        $mail->Username = getenv('SMTP_VER_EMAIL');
                        $mail->Password = getenv('SMTP_VER_EMAIL_PASSWORD');
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = getenv('SMTP_PORT');
                        $mail->CharSet = 'UTF-8';

                        $mail->setFrom(getenv('SMTP_VER_EMAIL'), 'Techoázis Regisztráció');
                        $mail->addAddress($email, $username);
                        $mail->isHTML(true); 
                        $mail->Subject = 'Aktiváld a Techoázis fiókodat!';

                        $activation_link = BASE_URL . "/views/activate.php?email=" . urlencode($email) . "&code=" . urlencode($activation_code);

                        $mail->Body = '
                            <div style="font-family: Arial, sans-serif;">
                                <h1>Köszönjük a regisztrációt!</h1>
                                <p>Kérlek, kattints az alábbi linkre a fiókod aktiválásához:</p>
                                <p>
                                    <a href="' . $activation_link . '" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Fiók aktiválása</a>
                                </p>
                                <p>Ha nem te regisztráltál, hagyd figyelmen kívül ezt az emailt.</p>
                            </div>';

                        $mail->send();
                        $_SESSION['registration_message'] = "Sikeres regisztráció! Kérlek, ellenőrizd az email címedet.";
                        header("Location: " . BASE_URL . "/views/login.php");
                        exit();
                    } catch (Exception $e) {
                        $errors[] = "Sikeres regisztráció, de az aktiváló emailt nem tudtuk kiküldeni.";
                    }
                } else {
                    $errors[] = "Hiba történt a mentés során.";
                }
                $stmt->close();
            }
        }
    }

    if (!empty($errors)) {
        $error_message = implode("<br>", array_map('htmlspecialchars', $errors));
    }
}

$siteKey = getenv('RECAPTCHA_SITE_KEY') ?: $_ENV['RECAPTCHA_SITE_KEY'];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg"> 
    <title>Techoázis | Regisztráció</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css"> 
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login_page.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $siteKey ?>"></script>
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

    <div class="background">
        <div class="login-container">
            <section class="login-box">
                <img src="<?= BASE_URL ?>/images/techoazis_palmtree.png" alt="Techoazis Logo" style="height: 90px; width: 115px; display: block; margin: 0 auto;">
                <h2>Regisztráció</h2>
                
                <?php if (!empty($error_message)) : ?>
                    <div class="login-alert"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form id="registrationForm" method="POST" action="">
                    <div class="login-form-group">
                        <label for="username" class="login-label">Felhasználónév</label>
                        <input type="text" name="username" id="username" class="login-input" placeholder="Felhasználónév" required>
                    </div>
                    <div class="login-form-group">
                        <label for="email" class="login-label">Email cím</label>
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

                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                    <button type="submit" class="login-button">Regisztráció</button>
                </form>
                
                <p class="login-separator">Van már fiókod?<a style="color: var(--accent-600); padding: 5px; margin-inline: 5px;" href="<?= BASE_URL ?>/views/login.php">Bejelentkezés</a></p>
                
                <p style="font-size: 10px; color: rgba(255,255,255,0.6); margin-top: 15px; text-align: center;">
                    Ezt az oldalt a reCAPTCHA védi. <br>
                    <a href="https://policies.google.com/privacy" style="color: #fff">Adatvédelem</a> és <a href="https://policies.google.com/terms" style="color: #fff">Feltételek</a>.
                </p>
            </section>
        </div>
    </div>

    <script>
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= $siteKey ?>', {action: 'registration'}).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
                HTMLFormElement.prototype.submit.call(form);
            });
        });
    });
    </script>
</body>
</html>