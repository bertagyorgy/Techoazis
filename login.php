<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <script src="index.js" defer></script>
    <title>Techoazis | Login</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<?php
    include 'db.php';
?>
    <div class="background">
        <div class="login-container">
            <section class="login-box">
                <h2>Bejelentkezés</h2>
                <?php if (!empty($error_message)) : ?>
                    <div class="login-alert"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="login-form-group">
                        <label for="username" class="login-label">Felhasználónév</label>
                        <input type="text" name="username" id="username" class="login-input" required>
                    </div>
                    <div class="login-form-group">
                        <label for="password" class="login-label">Jelszó</label>
                        <input type="password" name="password" id="password" class="login-input" required>
                    </div>
                    <button type="submit" name="login" class="login-button">Bejelentkezés</button>
                </form>

                <p class="login-footer">Nincs fiókod? <a href="registration.php">Regisztráció</a></p>
            </section>
        </div>
    </div>


</body>
</html>