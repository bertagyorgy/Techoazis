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
        $is_active = isset($_POST['is_active']) ? "A" : "IA";

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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <script src="index.js" defer></script>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="index.css">
    <style>
        body { font-family: "Segoe UI", Roboto, sans-serif; background-color: #f3f4f6; margin: 0; padding: 40px; color: #333; }
        h1 { text-align: center; color: #1f2937; margin-bottom: 20px; }
        a { color: #2563eb; text-decoration: none; font-weight: 500; }
        a:hover { text-decoration: underline; }
        .action-btn { background: #2563eb; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; transition: background 0.2s; }
        .action-btn:hover { background: #1e40af; }
        .delete-btn { background: #dc2626; }
        .delete-btn:hover { background: #b91c1c; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        th, td { padding: 12px 16px; text-align: left; }
        th { background: #2563eb; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:nth-child(even) { background-color: #f9fafb; }
        tr:hover { background-color: #eff6ff; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .add-user-btn { background: #16a34a; color: white; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background 0.2s; }
        .add-user-btn:hover { background: #15803d; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 6px; background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; text-align: center; }
        .message.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        /* ===============================
        USERS PAGE FIXES
        ================================*/
        body.users-page {
            background-color: var(--background-light);
            margin: 0;
            padding: 2rem;
            font-family: 'Sans-Serif', sans-serif;
        }

        .users-page h1 {
            text-align: center;
            color: var(--primary-color);
            margin-top: 2rem; /* hogy ne ütközzön a navbárral */
        }

        .users-table-wrapper {
            max-width: 1100px;
            margin: 2rem auto;
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .users-table th {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 0.75rem;
            text-align: left;
        }

        .users-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #ddd;
        }

        .users-table tr:hover {
            background-color: #f9f9f9;
        }

        .users-table a {
            color: var(--secondary-color);
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .users-table a:hover {
            color: #ff9966;
        }

    </style>
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
                    <th>Szerepkör</th>
                    <th>Aktív</th>
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
                        echo "<td>" . htmlspecialchars($row['user_role']) . "</td>";
                        echo "<td>" . ($row['is_active'] ? '✅ Igen' : '❌ Nem') . "</td>";
                        echo "<td>
                                <a href='users.php?action=edit&id={$row['user_id']}' class='action-btn'>Szerkesztés</a>
                                <a href='users.php?action=delete&id={$row['user_id']}' class='action-btn delete-btn' 
                                onclick='return confirm(\"Biztosan törlöd ezt a felhasználót?\");'>Törlés</a>
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
