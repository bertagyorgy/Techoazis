<?php
require 'auth_check.php';

// Alapértelmezett művelet: lista
$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? null;
$message = ''; 

// --- 1. MŰVELETEK KEZELÉSE (POST: Létrehozás, Frissítés) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- ÚJ FELHASZNÁLÓ (CREATE) ---
    if (isset($_POST['save_user'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $user_role = $_POST['user_role']; 
        $is_active = isset($_POST['is_active']) ? "A" : "IA";

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO USERS (username, email, user_password, user_role, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $user_role, $is_active);
            if ($stmt->execute()) {
                $message = "Felhasználó sikeresen létrehozva!";
                $action = 'list';
            } else {
                $message = "Hiba a mentés során: " . $stmt->error;
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $message = "Hiba: " . $e->getMessage();
        }
    }

    // --- FELHASZNÁLÓ FRISSÍTÉSE (UPDATE) ---
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $user_role = $_POST['user_role'];
        $is_active = isset($_POST['is_active']) ? "A" : "T";

        try {
            $sql = "UPDATE USERS SET username = ?, email = ?, user_role = ?, is_active = ? WHERE user_id = ?";
            $types = "sssii";
            $params = [$username, $email, $user_role, $is_active, $user_id];

            if (!empty($_POST['password'])) {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE USERS SET username = ?, email = ?, user_role = ?, is_active = ?, password = ? WHERE user_id = ?";
                $types = "sssisi";
                $params = [$username, $email, $user_role, $is_active, $hashed_password, $user_id];
            }

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $message = "Felhasználó sikeresen frissítve!";
                $action = 'list';
            } else {
                $message = "Hiba a frissítés során: " . $stmt->error;
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $message = "Hiba: " . $e->getMessage();
        }
    }
}

// --- 2. FELHASZNÁLÓ TÖRLÉSE ---
if ($action === 'delete' && $user_id) {
    $safe_user_id = (int)$user_id;
    try {
        $stmt = $conn->prepare("DELETE FROM USERS WHERE user_id = ?");
        $stmt->bind_param("i", $safe_user_id);
        $stmt->execute();
        $stmt->close();
        $message = "Felhasználó sikeresen törölve!";
        header("Location: users.php?message=" . urlencode($message));
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

$title = "Felhasználók Kezelése";
if ($action === 'add') $title = "Új Felhasználó";
if ($action === 'edit') $title = "Felhasználó Szerkesztése";
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <script src="index.js" defer></script>
    <title>Techoazis | Felhasználók</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="users.css">
</head>
<body class="users-page">

<?php if (!empty($message)): ?>
    <div class="message <?php echo str_starts_with(strtolower($message), 'hiba') ? 'error' : ''; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php
switch ($action):

case 'add': ?>
    <div class="form-container">
        <h1>Új felhasználó hozzáadása</h1>
        <form action="users.php" method="POST">
            <input type="hidden" name="save_user" value="1">
            <label>Felhasználónév: <input type="text" name="username" required></label><br><br>
            <label>Email: <input type="email" name="email" required></label><br><br>
            <label>Jelszó: <input type="password" name="password" required></label><br><br>
            <label>Szerepkör: <input type="text" name="user_role" value="user" required></label><br><br>
            <label><input type="checkbox" name="is_active" value="A" checked> Aktív</label><br><br>
            <button type="submit" class="action-btn">Mentés</button>
            <a href="users.php" class="action-btn delete-btn">Mégse</a>
        </form>
    </div>
<?php
break;

case 'edit':
    if ($user_id) {
        $stmt = $conn->prepare("SELECT user_id, username, email, user_role, is_active FROM USERS WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    }
    if ($user):
?>
    <div class="form-container">
        <h1>Felhasználó szerkesztése: <?php echo htmlspecialchars($user['username']); ?></h1>
        <form action="users.php" method="POST">
            <input type="hidden" name="update_user" value="1">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <label>Felhasználónév: <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></label><br><br>
            <label>Email: <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></label><br><br>
            <label>Szerepkör: <input type="text" name="user_role" value="<?php echo htmlspecialchars($user['user_role']); ?>" required></label><br><br>
            <label><input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>> Aktív</label><br><br>
            <label>Új jelszó (opcionális): <input type="password" name="password"></label><br><br>
            <button type="submit" class="action-btn">Frissítés</button>
            <a href="users.php" class="action-btn delete-btn">Mégse</a>
        </form>
    </div>
<?php else: ?>
    <p class="message error">Felhasználó nem található.</p>
<?php endif;
break;

default: ?>
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
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['user_id']}</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . ($row['is_active'] === 'A' ? '✅ Aktív' : '❌ Törölt') . "</td>";
                        echo "<td>" . htmlspecialchars($row['user_role']) . "</td>";
                        echo "<td>
                                <a href='users.php?action=edit&id={$row['user_id']}' class='action-btn'><i class='fa-solid fa-pen-to-square'></i> Szerkesztés</a>
                                <a href='users.php?action=delete&id={$row['user_id']}' class='action-btn delete-btn' 
                                onclick='return confirm(\"Biztosan törlöd ezt a felhasználót?\");'><i class='fa-solid fa-box-archive'></i> Törlés</a>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>Nincsenek felhasználók.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <a href="admin_panel.php" class="back-link">⬅ Vissza a panelra</a>
<?php
endswitch;
$conn->close();
?>
</body>
</html>
