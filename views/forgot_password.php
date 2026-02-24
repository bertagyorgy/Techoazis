<?php
// views/forgot_password.php
if (session_status() === PHP_SESSION_NONE) {
    @session_start(); // @ jel a hibaüzenetek elnyomására (cPanel hiba kivédése)
}
ob_start();

// Időzóna beállítása biztos, ami biztos
date_default_timezone_set('Europe/Budapest');

require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/core/envreader.php';
require_once ROOT_PATH . '/app/auth_check_login.php';
loadEnv(); // Környezeti változók betöltése rögtön az elején

require_once ROOT_PATH . '/core/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$message_type = '';

// A beküldést az email mező meglétével ellenőrizzük, mert a submit gombnak nincs neve
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    
    // --- RECAPTCHA V3 ELLENŐRZÉS ---
    $secretKey = getenv('RECAPTCHA_SECRET_KEY') ?: $_ENV['RECAPTCHA_SECRET_KEY']; 
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($captchaResponse)) {
        $message = "Biztonsági ellenőrzés sikertelen (hiányzó token).";
        $message_type = "error";
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
            $message = "A rendszer gyanús tevékenységet észlelt. Próbáld újra!";
            $message_type = "error";
        } else {
            // --- HA A CAPTCHA SIKERES, JÖHET AZ EMAIL KÜLDÉS ---
            $email = trim($_POST['email']);

            if (empty($email)) {
                $message = "Kérlek, add meg az e-mail címed.";
                $message_type = "error";
            } else {
                // JAVÍTÁS: LOWER() használata az egyezéshez
                $sql = "SELECT user_id FROM users WHERE LOWER(email) = LOWER(?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $token = bin2hex(random_bytes(32));
                        $token_hash = hash('sha256', $token);
                        
                        // JAVÍTÁS: Nem a PHP számolja az időt, hanem a MySQL a DATE_ADD(NOW(), INTERVAL 1 HOUR) segítségével!
                        $update_sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE LOWER(email) = LOWER(?)";
                        if ($update_stmt = $conn->prepare($update_sql)) {
                            // Ide már csak a token_hash és az email kell, mert a dátum be van égetve az SQL-be
                            $update_stmt->bind_param("ss", $token_hash, $email);
                            
                            if ($update_stmt->execute()) {
                                
                                $mail = new PHPMailer(true);
                                try {
                                    $mail->isSMTP();
                                    $mail->Host       = getenv('SMTP_HOST');
                                    $mail->SMTPAuth   = true;
                                    $mail->Username   = getenv('SMTP_VER_EMAIL');
                                    $mail->Password   = getenv('SMTP_VER_EMAIL_PASSWORD');
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail->Port       = getenv('SMTP_PORT');
                                    $mail->CharSet    = 'UTF-8';

                                    $mail->setFrom(getenv('SMTP_VER_EMAIL'), 'Techoázis Support');
                                    $mail->addAddress($email);
                                    $mail->isHTML(true);

                                    $reset_link = BASE_URL . "/views/reset_password.php?token=" . $token . "&email=" . urlencode($email);

                                    $mail->Subject = 'Jelszó visszaállítása - Techoázis';
                                    $mail->Body    = '
                                        <div style="font-family: Arial, sans-serif;">
                                            <h2>Jelszó visszaállítása</h2>
                                            <p>Kattints az alábbi linkre a jelszavad visszaállításához (a link 1 óráig érvényes):</p>
                                            <p><a href="' . $reset_link . '" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Új jelszó megadása</a></p>
                                            <p>Ha nem te kérted a visszaállítást, hagyd figyelmen kívül ezt az emailt.</p>
                                        </div>';

                                    $mail->send();
                                    $message = "Elküldtük a visszaállító linket az email címedre.";
                                    $message_type = "success";

                                } catch (Exception $e) {
                                    $message = "Hiba az email küldésekor: " . $mail->ErrorInfo;
                                    $message_type = "error";
                                }
                            }
                            $update_stmt->close();
                        }
                    } else {
                        // Biztonsági okokból ugyanazt az üzenetet adjuk, ha nincs ilyen email
                        $message = "Ha létezik ez az email cím a rendszerben, elküldtük a linket.";
                        $message_type = "success";
                    }
                    $stmt->close();
                }
            }
        }
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
    <title>Techoázis | Jelszó visszaállítása</title>
    
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
                <h2>Jelszó visszaállítása</h2>
                <p style="text-align: center; color: white; margin-bottom: 20px;">Kérlek, add meg az e-mail címed, amivel regisztráltál, és elküldjük a visszaállító linket.</p>

                <?php if (!empty($message)) : ?>
                    <div class="login-alert <?= $message_type === 'success' ? 'login-success' : '' ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form id="forgotForm" method="POST" action="">
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                    <div class="login-form-group">
                        <label for="email" class="login-label">E-mail cím</label>
                        <input type="email" name="email" id="email" class="login-input" placeholder="E-mail cím" required>
                    </div>

                    <button type="submit" class="login-button">Link küldése</button>
                </form>
                
                <p class="login-separator"><a style="color: white; text-decoration: underline;" href="<?= BASE_URL ?>/views/login.php">Vissza a bejelentkezéshez</a></p>

                <p style="font-size: 10px; color: rgba(255,255,255,0.6); margin-top: 15px; text-align: center;">
                    Ezt az oldalt a reCAPTCHA védi. <br>
                    <a href="https://policies.google.com/privacy" style="color: #fff">Adatvédelem</a> és <a href="https://policies.google.com/terms" style="color: #fff">Feltételek</a>.
                </p>
            </section>
        </div>
    </div>

    <script>
    if (document.getElementById('forgotForm')) {
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            
            grecaptcha.ready(function() {
                grecaptcha.execute('<?= $siteKey ?>', {action: 'forgot_password'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    HTMLFormElement.prototype.submit.call(form);
                });
            });
        });
    }
    </script>
</body>
</html>