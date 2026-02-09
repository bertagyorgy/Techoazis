<?php
// views/reset_password.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $base_url = 'http://localhost/techoazis/';
// ROOT_PATH = '/techoazis/';
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/db.php';

$message = '';
$error = '';
$token_valid = false;

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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
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
                $_SESSION['registration_message'] = "A jelszavad sikeresen megváltozott! Most már bejelentkezhetsz.";
                header("Location: " . BASE_URL . "/views/login.php");
                exit();
            } else {
                $error = "Hiba történt vagy a link már érvénytelen.";
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
    <title>Techoazis | Új jelszó</title>
    
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
</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>
    <div class="background">
        <div class="login-container">
            <section class="login-box">
                <h2>Új jelszó megadása</h2>
                
                <?php if (!empty($error)) : ?>
                    <div class="login-alert"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($token_valid) : ?>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

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
                    <button type="submit" name="submit" class="login-button">Jelszó mentése</button>
                </form>
                <?php else: ?>
                    <p class="login-footer" style="margin-top:20px;"><a href="<?= BASE_URL ?>/views/forgot_password.php">Új visszaállító link kérése</a></p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>