<?php
session_start();
require_once __DIR__ . '/app/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'send':
        sendMessage($conn, $user_id);
        break;

    case 'get':
        getMessages($conn, $user_id);
        break;

    case 'mark_read':
        markMessagesAsRead($conn, $user_id);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Érvénytelen művelet']);
        break;
}

/* =========================
   ÜZENET KÜLDÉS
========================= */
function sendMessage(mysqli $conn, int $user_id): void
{
    $conversation_id = (int)($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($conversation_id <= 0 || $message === '') {
        echo json_encode(['success' => false, 'error' => 'Hiányzó adatok']);
        return;
    }

    // jogosultság ellenőrzés
    $stmt = $conn->prepare("
        SELECT conversation_id
        FROM conversations
        WHERE conversation_id = ?
          AND (seller_user_id = ? OR buyer_user_id = ?)
        LIMIT 1
    ");
    $stmt->bind_param("iii", $conversation_id, $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Hozzáférés megtagadva']);
        return;
    }

    // üzenet mentése
    $stmt = $conn->prepare("
        INSERT INTO messages (conversation_id, sender_user_id, user_message)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $conversation_id, $user_id, $message);
    $stmt->execute();
    $message_id = $stmt->insert_id;
    $stmt->close();

    // beszélgetés frissítése
    $stmt = $conn->prepare("
        UPDATE conversations
        SET updated_at = NOW()
        WHERE conversation_id = ?
    ");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message_id' => $message_id
    ]);
}

/* =========================
   ÚJ ÜZENETEK LEKÉRÉSE
========================= */
function getMessages(mysqli $conn, int $user_id): void
{
    $conversation_id = (int)($_GET['conversation_id'] ?? 0);
    $last_id = (int)($_GET['last_id'] ?? 0);

    if ($conversation_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Hiányzó beszélgetés ID']);
        return;
    }

    // jogosultság
    $stmt = $conn->prepare("
        SELECT conversation_id
        FROM conversations
        WHERE conversation_id = ?
          AND (seller_user_id = ? OR buyer_user_id = ?)
        LIMIT 1
    ");
    $stmt->bind_param("iii", $conversation_id, $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Hozzáférés megtagadva']);
        return;
    }

    $sql = "
        SELECT 
            m.message_id,
            m.sender_user_id,
            m.user_message,
            m.sent_at,
            m.is_read,
            u.username,
            u.profile_image
        FROM messages m
        JOIN users u ON m.sender_user_id = u.user_id
        WHERE m.conversation_id = ?
          AND m.message_id > ?
        ORDER BY m.sent_at ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $conversation_id, $last_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
}

/* =========================
   OLVASOTTRA JELÖLÉS
========================= */
function markMessagesAsRead(mysqli $conn, int $user_id): void
{
    $conversation_id = (int)($_POST['conversation_id'] ?? 0);

    if ($conversation_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Hiányzó beszélgetés ID']);
        return;
    }

    $stmt = $conn->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE conversation_id = ?
          AND sender_user_id != ?
    ");
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
}
