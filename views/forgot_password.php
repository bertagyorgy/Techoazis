<?php
// views/forgot_password.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BASE_URL = 'http://localhost/techoazis/';
// ROOT_PATH = '/techoazis/';
require_once __DIR__ . '/../core/config.php';

require_once ROOT_PATH . '/app/db.php';
require_once ROOT_PATH . '/envreader.php';
require_once ROOT_PATH . '/core/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Kérlek, add meg az e-mail címed.";
        $message_type = "error";
    } else {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expiry = date("Y-m-d H:i:s", time() + 3600);

                $update_sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("sss", $token_hash, $expiry, $email);
                    if ($update_stmt->execute()) {
                        
                        $mail = new PHPMailer(true);
                        try {
                            loadEnv(); 
                            $mail->isSMTP();
                            $mail->Host = getenv('SMTP_HOST');
                            $mail->SMTPAuth = true;
                            $mail->Username = getenv('SMTP_VER_EMAIL');
                            $mail->Password = getenv('SMTP_VER_EMAIL_PASSWORD');
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = getenv('SMTP_PORT');

                            // JAVÍTÁS: Karakterkódolás beállítása
                            $mail->CharSet = 'UTF-8';
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
                            $message = "Hiba az email küldésekor.";
                        }
                    }
                    $update_stmt->close();
                }
            } else {
                $message = "Ha létezik ez az email cím a rendszerben, elküldtük a linket.";
                $message_type = "success";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" /> 
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <title>Techoazis | Elfelejtett jelszó</title>
    
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
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>
    <div class="background">
        <div class="login-container">
            <section class="login-box">
                <h2>Elfelejtett jelszó</h2>
                
                <?php if (!empty($message)) : ?>
                    <div class="login-alert <?php echo ($message_type === 'success') ? 'login-success' : ''; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="login-form-group">
                        <label for="email" class="login-label">Email cím</label>
                        <input type="email" name="email" id="email" class="login-input" placeholder="Email cím" required>
                    </div>
                    <button type="submit" name="submit" class="login-button">Link küldése</button>
                </form>

                <a style="color: white;" href="<?= BASE_URL ?>/views/login.php">Vissza a bejelentkezéshez</a>
            </section>
        </div>
    </div>
</body>
</html>