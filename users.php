<?php
require 'auth_check.php';

$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? null;
$message = '';
if (empty($message) && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

// --- 1. MŰVELETEK KEZELÉSE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $user_role = $_POST['user_role'];
        $is_active = isset($_POST['is_active']) ? "A" : "IA";

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO USERS (username, email, user_password, user_role, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $user_role, $is_active);
            $stmt->execute();
            $message = "Felhasználó sikeresen létrehozva!";
            $action = 'list';
            $stmt->close();

            echo "<script>window.location.href='users.php?message=' + encodeURIComponent('Felhasználó sikeresen létrehozva!');</script>";
            exit();
        } catch (mysqli_sql_exception $e) {
            $message = "Hiba: " . $e->getMessage();
        }
    }

    if (isset($_POST['update_user'])) {
        $user_id = (int)$_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $user_role = trim($_POST['user_role']);
        $is_active = isset($_POST['is_active']) ? "A" : "T";

        $sql = "UPDATE USERS SET username=?, email=?, user_role=?, is_active=?";
        $params = [$username, $email, $user_role, $is_active];
        $types = "ssss";

        if (!empty($_POST['password'])) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql .= ", user_password=?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        $sql .= " WHERE user_id=?";
        $params[] = $user_id;
        $types .= "i";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $message = "Felhasználó sikeresen frissítve!";
            $stmt->close();

            echo "<script>window.location.href='users.php?message=' + encodeURIComponent('Felhasználó sikeresen frissítve!');</script>";
            exit();
        } catch (mysqli_sql_exception $e) {
            $message = "Hiba a frissítés során: " . $e->getMessage();
        }
        $action = 'list';
    }
}

// --- 2. TÖRLÉS ---
if ($action === 'delete' && $user_id) {
    $safe_user_id = (int)$user_id;
    try {
        $stmt = $conn->prepare("DELETE FROM USERS WHERE user_id = ?");
        $stmt->bind_param("i", $safe_user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: users.php?message=" . urlencode("Felhasználó sikeresen törölve!"));
        exit;
    } catch (mysqli_sql_exception $e) {
        $message = "Hiba a törlés során: " . $e->getMessage();
    }
}

if (empty($message) && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

// Navbar
$page = $_GET['p'] ?? '';
if ($page === '') {
    include 'navbar.php';
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Techoazis | Felhasználók</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="users.css">
    <script defer src="index.js"></script>
    <style>
        body { opacity: 0; transition: opacity 0.3s ease; }
        body.loaded { opacity: 1; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.body.classList.add("loaded");
        });
    </script>
</head>
<body class="users-page">

<?php if (!empty($message)): ?>
    <div class="message <?php echo str_starts_with(strtolower($message), 'hiba') ? 'error' : ''; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php switch ($action): case 'add': ?>
    <div class="form-container">
        <h1>Új felhasználó hozzáadása</h1>
        <form method="POST">
            <input type="hidden" name="save_user" value="1">
            <label>Felhasználónév: <input type="text" name="username" required></label>
            <label>Email: <input type="email" name="email" required></label>
            <label>Jelszó: <input type="password" name="password" required></label>
            <label>Szerepkör: <input type="text" name="user_role" value="F" required></label>
            <label class="checkbox-label"><input type="checkbox" name="is_active" value="A" checked> Aktív</label>
            <button type="submit" class="action-btn">Mentés</button>
            <a href="users.php" class="action-btn delete-btn">Mégse</a>
        </form>
    </div>

<?php break; case 'edit':
    if ($user_id) {
        $stmt = $conn->prepare("SELECT user_id, username, email, user_role, is_active FROM USERS WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if ($user): ?>
    <div class="form-container">
        <h1>Felhasználó szerkesztése: <?php echo htmlspecialchars($user['username']); ?></h1>
        <form method="POST">
            <input type="hidden" name="update_user" value="1">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <label>Felhasználónév: <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></label>
            <label>Email: <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></label>
            <label>Szerepkör: <input type="text" name="user_role" value="<?php echo htmlspecialchars($user['user_role']); ?>" required></label>
            <label class="checkbox-label"><input type="checkbox" name="is_active" <?php echo $user['is_active'] === 'A' ? 'checked' : ''; ?>> Aktív</label>
            <label>Új jelszó (opcionális): <input type="password" name="password"></label>
            <button type="submit" class="action-btn">Frissítés</button>
            <a href="users.php" class="action-btn delete-btn">Mégse</a>
        </form>
    </div>
    <?php else: ?>
        <p class="message error">Felhasználó nem található.</p>
    <?php endif; break; default: ?>

    <div class="top-bar">
        <h1>Felhasználók Kezelése</h1>
        <a href="users.php?action=add" class="add-user-btn">+ Új felhasználó</a>
    </div>

    <div class="users-table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Státusz</th>
                    <th>Szerepkör</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT user_id, username, email, user_role, is_active FROM USERS ORDER BY user_id");
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['user_id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['is_active'] === 'A' ? '✅ Aktív' : '❌ Törölt' ?></td>
                    <td><?= htmlspecialchars($row['user_role']) ?></td>
                    <td>
                        <a href="users.php?action=edit&id=<?= $row['user_id'] ?>" class="action-btn"><i class="fa-solid fa-pen-to-square"></i> Szerkesztés</a>
                        <a href="users.php?action=delete&id=<?= $row['user_id'] ?>" class="action-btn delete-btn"
                        onclick="return confirm('Biztosan törlöd ezt a felhasználót?');"><i class="fa-solid fa-box-archive"></i> Törlés</a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" style="text-align:center; padding:20px;">Nincsenek felhasználók.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="admin_panel.php" class="back-link">⬅ Vissza a panelra</a>
<?php endswitch; $conn->close(); ?>
</body>
</html>
