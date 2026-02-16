<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

// A config behívása a szülőmappából
require_once __DIR__ . '/../core/config.php';

// Adatbázis behívása
require_once ROOT_PATH . '/app/db.php';


$info_message = '';
if (isset($_SESSION['registration_message'])) {
    $info_message = $_SESSION['registration_message'];
    unset($_SESSION['registration_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />  
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <title>Techoázis | Bejelentkezés</title>
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
<?php
include ROOT_PATH . '/views/navbar.php';

$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Kérlek, tölts ki minden mezőt.";
    } else {
        $sql = "SELECT user_id, username, email, user_password, is_active, user_role FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();

                    if ($row['is_active'] !== 'A') {
                        if ($row['is_active'] === 'P') {
                            $error_message = "A fiókod még NINCS MEGERŐSÍTVE.";
                        } elseif ($row['is_active'] === 'T') {
                            $error_message = "A fiókod törölve lett.";
                        } else {
                            $error_message = "A fiókod nem aktív."; 
                        }
                    } elseif (password_verify($password, $row['user_password'])) {
                        $user_id = $row['user_id'];
                        $login_date = date('Y-m-d H:i:s');

                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['user_role'] = $row['user_role'];
                        $_SESSION['loggedin'] = true;

                        $insert_sql = "INSERT INTO login (user_id, login_date) VALUES (?, ?)";
                        if ($insert_stmt = $conn->prepare($insert_sql)) {
                            $insert_stmt->bind_param("is", $user_id, $login_date);
                            $insert_stmt->execute();
                            $insert_stmt->close();
                        }
                        
                        // JAVÍTÁS: Pont hozzáadva a kiterjesztés elé és fix BASE_URL  összefűzés
                        if (isset($_SESSION['redirect_after_login'])) {
                            $url = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                            header("Location: " . BASE_URL  . "/". $url . ".php");
                            exit();
                        }
                        

                        if (isset($_POST['remember'])) {

                            $token = bin2hex(random_bytes(32));
                            $expire = date('Y-m-d H:i:s', strtotime('+30 days'));

                            $stmt2 = $conn->prepare("UPDATE users SET remember_token=?, remember_expire=? WHERE user_id=?");
                            $stmt2->bind_param("ssi", $token, $expire, $row['user_id']);
                            $stmt2->execute();

                            setcookie(
                                "remember_token",
                                $token,
                                time() + (86400 * 30),
                                "/",
                                "",
                                false,  // XAMPP-nál ne legyen true mert nincs HTTPS
                                true
                            );
                        }


                        header("Location: " . BASE_URL  . "/" . "index.php");
                        exit();
                        
                    } else {
                        $error_message = "Hibás jelszó.";
                    }
                } else {
                    $error_message = "Nincs ilyen felhasználónév.";
                }
            } else {
                $error_message = "Hiba történt a bejelentkezés során.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

    <div class="background">
        <div class="login-container">
            <section class="login-box">
                <img src="<?= BASE_URL ?>/images/techoazis_palmtree.png" alt="Techoazis Logo" style="height: 90px; width: 115px; display: block; margin: 0 auto;">
                <h2>Bejelentkezés</h2>
                
                <?php if (!empty($info_message)) : ?>
                    <div class="login-alert login-success"><?php echo $info_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)) : ?>
                    <div class="login-alert"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="login-form-group">
                        <label for="username" class="login-label">Felhasználónév</label>
                        <input type="text" name="username" id="username" class="login-input" placeholder="Felhasználónév" required>
                    </div>
                    <div class="login-form-group">
                        <label for="password" class="login-label">Jelszó</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="login-input" placeholder="Jelszó" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                        <div class="login-options">
                            <div class="remember-box">
                                <input type="checkbox" name="remember" id="remember">
                                <label for="remember">Emlékezz rám</label>
                            </div>

                            <a style="color: white; font-size: 14px;" href="<?= BASE_URL ?>/views/forgot_password.php">Elfelejtett jelszó?</a>
                        </div>
                    </div>
                    <button type="submit" name="submit" class="login-button">Bejelentkezés</button>
                </form>
                
                <p class="login-separator">Nincs fiókod?<a style="color: black" href="<?= BASE_URL ?>/views/registration.php"> Regisztráció</a></p>

                <!--<a href="<= BASE_URL ?>/views/registration.php"><button class="registration-button">Regisztráció</button></a>-->
            </section>
        </div>
    </div>
</body>
</html>