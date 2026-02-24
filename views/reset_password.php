<?php
// views/reset_password.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

// 1. Config és Env betöltése (pontosan mint a login.php-ban)
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/envreader.php';
require_once ROOT_PATH . '/app/auth_check_login.php';
loadEnv();

// Adatbázis behívása
require_once ROOT_PATH . '/app/db.php';

$message = '';
$error = '';
$token_valid = false;

// Token és email ellenőrzése (GET)
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    $token_hash = hash('sha256', $token);

    $sql = "SELECT user_id FROM users WHERE email = ? AND reset_token_hash = ? AND reset_token_expires_at > NOW()";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $email, $token_hash);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $token_valid = true;
        } else {
            $error = "Érvénytelen vagy lejárt link. Kérlek, kérj újat.";
        }
        $stmt->close();
    }
}

// Form feldolgozása (POST) - A login.php logikájára építve
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['password'])) {
    
    // --- RECAPTCHA V3 ELLENŐRZÉS ---
    $secretKey = getenv('RECAPTCHA_SECRET_KEY') ?: $_ENV['RECAPTCHA_SECRET_KEY']; 
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($captchaResponse)) {
        $error = "Biztonsági ellenőrzés hiba (hiányzó token).";
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
            $error = "A rendszer gyanús tevékenységet észlelt. Próbáld újra!";
            $token_valid = true; 
        } else {
            // HA SIKERES A CAPTCHA, JÖHET A JELSZÓ MÓDOSÍTÁS
            $token = $_POST['token'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $token_hash = hash('sha256', $token);

            if ($password !== $confirm_password) {
                $error = "A jelszavak nem egyeznek.";
                $token_valid = true;
            } elseif (strlen($password) < 6) {
                $error = "A jelszó legyen legalább 6 karakter.";
                $token_valid = true;
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET user_password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE email = ? AND reset_token_hash = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("sss", $hashed_password, $email, $token_hash);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $_SESSION['registration_message'] = "A jelszavad sikeresen megváltozott! Jelentkezz be.";
                        header("Location: " . BASE_URL . "/views/login.php");
                        exit();
                    } else {
                        $error = "Hiba történt a mentés során.";
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
    <title>Techoázis | Új jelszó</title>
    
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
                <h2>Új jelszó</h2>
                
                <?php if (!empty($error)) : ?>
                    <div class="login-alert"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($token_valid) : ?>
                <form id="resetForm" method="POST" action="">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                    <div class="login-form-group">
                        <label for="password" class="login-label">Új jelszó</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="login-input" placeholder="Új jelszó" required>
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

                    <button type="submit" class="login-button">Jelszó mentése</button>
                </form>
                <?php else: ?>
                    <p style="text-align:center; color:white; margin-top:20px;">
                        <a href="<?= BASE_URL ?>/views/forgot_password.php" style="color: #fff; text-decoration: underline;">Új visszaállító link kérése</a>
                    </p>
                <?php endif; ?>
                
                <p style="font-size: 10px; color: rgba(255,255,255,0.6); margin-top: 15px; text-align: center;">
                    Ezt az oldalt a reCAPTCHA védi. <br>
                    <a href="https://policies.google.com/privacy" style="color: #fff">Adatvédelem</a> és <a href="https://policies.google.com/terms" style="color: #fff">Feltételek</a>.
                </p>
            </section>
        </div>
    </div>

    <script>
    // Csak akkor fut le, ha az űrlap létezik (érvényes token esetén)
    if (document.getElementById('resetForm')) {
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            
            grecaptcha.ready(function() {
                grecaptcha.execute('<?= $siteKey ?>', {action: 'reset_password'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    // A login.php-ban bevált beküldési mód
                    HTMLFormElement.prototype.submit.call(form);
                });
            });
        });
    }
    </script>
</body>
</html>