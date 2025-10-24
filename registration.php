<?php
session_start();
include 'db.php';
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
    <title>Techoazis | Registration</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<?php
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $errors = [];

    // alapadatok
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // validációk
    if ($username === '') {
        $errors[] = "Kérlek, add meg a felhasználónevet.";
    } elseif (strlen($username) < 3) {
        $errors[] = "A felhasználónév legyen legalább 3 karakter.";
    }

    if ($email === '') {
        $errors[] = "Kérlek, add meg az e-mail címed.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Érvénytelen e-mail cím formátum.";
    }

    if ($password === '') {
        $errors[] = "Kérlek, add meg a jelszót.";
    } elseif (strlen($password) < 6) {
        $errors[] = "A jelszó legyen legalább 6 karakter.";
    }

    if ($confirm_password === '') {
        $errors[] = "Kérlek, erősítsd meg a jelszót.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "A jelszavak nem egyeznek.";
    }

    // ha eddig nincs validációs hiba, nézzük az adatbázist
    if (empty($errors)) {
        // Keresés: username vagy email foglalt-e
        $sql = "SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $email);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = "Ez a felhasználónév vagy e-mail már foglalt.";
                }
            } else {
                $errors[] = "Adatbázis hiba: nem sikerült ellenőrizni a meglévő felhasználókat.";
            }
            $stmt->close();
        } else {
            $errors[] = "Adatbázis hiba: előkészítés nem sikerült.";
        }
    }

    // Ha még mindig nincs hiba, beszúrjuk a felhasználót
    if (empty($errors)) {
        $sql = "INSERT INTO users (username, email, user_password, is_active, registration_date, user_role, ip)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_active = 'A';
            $registration_date = date("Y-m-d H:i:s");
            $user_role = 'F';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $stmt->bind_param(
                "sssssss",
                $username,
                $email,
                $hashed_password,
                $is_active,
                $registration_date,
                $user_role,
                $ip
            );

            if ($stmt->execute()) {
                // sikeres regisztráció
                echo "<script>alert('Sikeres belépés!'); window.location.href='login.php';</script>";
                exit();
            } else {
                $errors[] = "Hiba a mentés során. Kérlek, próbáld újra később.";
            }

            $stmt->close();
        } else {
            $errors[] = "Adatbázis hiba: nem sikerült előkészíteni az insert lekérdezést.";
        }
    }

    // összefűzzük az összes hibát egy változóba, így biztosan megjelenik a frontendben
    if (!empty($errors)) {
        // HTML biztonság: a hibákat HTML-ben jelenítjük meg, ezért engedélyezett a <br> tag.
        $error_message = implode("<br>", array_map('htmlspecialchars', $errors));
    }

    // Ne zárjuk le a kapcsolatot itt, ha később még használod az oldalon
    // $conn->close();
}
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
                        <label for="password" class="login-label">Email</label>
                        <input type="email" name="email" id="email" class="login-input" required>
                    </div>
                    <div class="login-form-group">
                        <label for="password" class="login-label">Jelszó</label>
                        <input type="password" name="password" id="password" class="login-input" required>
                    </div>
                    <div class="login-form-group">
                        <label for="password" class="login-label">Jelszó megerősítése</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="login-input" required>
                    </div>
                    <button type="submit" name="submit" class="login-button">Regisztráció</button>
                </form>
            </section>
        </div>
    </div>


</body>
</html>