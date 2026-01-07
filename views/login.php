<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

$base_url = 'http://localhost/techoazis/';
$root_path = '/techoazis/';
// 1. JAVÍTÁS: Abszolút elérési út az 'app/db.php'-hoz
// __DIR__ a 'views' mappa. '/../' felvisz a gyökérbe (Techoazis).
include __DIR__ . '/../app/db.php'; 

// ÚJ RÉSZ: Sikeres regisztráció session üzenetének kezelése
$info_message = '';
if (isset($_SESSION['registration_message'])) {
    $info_message = $_SESSION['registration_message'];
    unset($_SESSION['registration_message']); // Töröljük a megjelenés után
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="icon" type="image/x-icon" href="../images/palmtree_favicon.svg">
    <script src="../static/index.js" defer></script>
    <title>Techoazis | Login</title>
    <link rel="stylesheet" href="../static/index.css">
    <link rel="stylesheet" href="../static/reset&base_styles.css">
    <link rel="stylesheet" href="../static/animations_microinteractions.css">
    <link rel="stylesheet" href="../static/button_system.css">
    <link rel="stylesheet" href="../static/login_page.css">
    <link rel="stylesheet" href="../static/modern_navbar.css">
    <link rel="stylesheet" href="../static/reset&base_styles.css">
    <link rel="stylesheet" href="../static/container&grid_system.css">


</head>
<body>
<?php
// 3. JAVÍTÁS: Navbar beillesztése (mivel a navbar is a views mappában van)
include __DIR__ . '/navbar.php';

$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Kérlek, tölts ki minden mezőt.";
    } else {
        // Ellenőrizzük, hogy van-e ilyen felhasználó
        $sql = "SELECT user_id, username, email, user_password, is_active, user_role FROM users WHERE username = ?";
        // A $conn változó a fent beillesztett db.php fájlból származik
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();

                    // MÓDOSÍTOTT RÉSZ: Ellenőrizzük az aktivitást
                    if ($row['is_active'] !== 'A') {
                        if ($row['is_active'] === 'P') {
                            $error_message = "A fiókod még NINCS MEGERŐSÍTVE. Kérlek, ellenőrizd az emailjeidet (beleértve a spam mappát is) az aktiváló linkért.";
                        } elseif ($row['is_active'] === 'T') {
                            $error_message = "A fiókod törölve lett. Ha esetleges hiba történt, akkor kérlek lépj kapcsolatba adminjainkkal";
                        } else {
                            $error_message = "A fiókod nem aktív."; 
                        }
                    } elseif (password_verify($password, $row['user_password'])) {
                        // Sikeres belépés (csak az 'A' állapotú felhasználó léphet be)

                        // Login adatainak mentése
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
                        
                        if (isset($_SESSION['redirect_after_login'])) {
                            $url = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                            header("Location: {$root_path}{$url}.php");
                            exit();
                        }

                        header("Location: {$root_path}index.php");
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
                        <input type="text" name="username" id="username" class="login-input" required>
                    </div>
                    <div class="login-form-group">
                        <label for="password" class="login-label">Jelszó</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="login-input" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                    <button type="submit" name="submit" class="login-button">Bejelentkezés</button>
                </form>

                <p class="login-footer">Nincs fiókod? <a href="registration.php">Regisztráció</a></p>
            </section>
        </div>
    </div>
</body>
</html>