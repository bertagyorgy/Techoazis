<?php
session_start();
require_once __DIR__ . '/app/db.php';

/* =========================
   1. AUTH
========================= */
if (!isset($_SESSION['user_id'])) {
    // Ha AJAX kérés jön de lejárt a session, JSON hibát küldünk
    if (isset($_REQUEST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Auth required']);
        exit();
    }
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ID-k előkészítése
$product_id = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
$conversation_id = isset($_REQUEST['conv_id']) ? (int)$_REQUEST['conv_id'] : 0;

/* =========================
   1.3 AJAX HANDLER (ÚJ RÉSZ)
   Ez szolgálja ki a JavaScript fetch kéréseit JSON formátumban.
   Így nem töltődik újra az oldal, és működik a chat.
========================= */
if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') {
    header('Content-Type: application/json');

    // --- A) ÜZENET KÜLDÉSE ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
        $msg = trim($_POST['user_message'] ?? '');
        
        if ($msg && $conversation_id > 0) {
            $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_user_id, user_message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $conversation_id, $user_id, $msg);
            
            if ($stmt->execute()) {

                // beszélgetés aktivitás frissítése
                $up = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE conversation_id = ?");
                $up->bind_param("i", $conversation_id);
                $up->execute();
                $up->close();
                echo json_encode(['success' => true, 'message_id' => $conn->insert_id]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Adatbázis hiba']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Hiányzó üzenet vagy ID']);
        }
        exit(); // Fontos: Itt megállunk, nem renderelünk HTML-t!
    }

    // --- B) ÜZENETEK LEKÉRÉSE & LÁTTAMOZÁS (POLLING/PING) ---
    if (isset($_GET['action']) && ($_GET['action'] === 'get_messages' || isset($_GET['ping']))) {
        
        if ($conversation_id > 0) {
            // 1. LÉPÉS: LÁTTAMOZÁS
            // Minden olyan üzenetet, ami EBBEN a beszélgetésben van,
            // NEM én küldtem, és még nincs olvasva, átállítjuk olvasottra.
            // (Nem kell tudnunk a másik user ID-ját, elég hogy 'sender_user_id != me')
            $stmt = $conn->prepare("
                UPDATE messages 
                SET is_read = 1 
                WHERE conversation_id = ? 
                  AND sender_user_id != ? 
                  AND is_read = 0
            ");
            $stmt->bind_param("ii", $conversation_id, $user_id);
            $stmt->execute();
            $stmt->close();

            // Ha csak ping volt (státusz frissítés), végzünk is
            if (isset($_GET['ping']) && !isset($_GET['action'])) {
                echo json_encode(['success' => true]);
                exit();
            }

            // 2. LÉPÉS: LEKÉRÉS
            // Visszaküldjük az üzeneteket (most már a frissített státuszokkal)
            $stmt = $conn->prepare("
                SELECT 
                    m.message_id, 
                    m.conversation_id, 
                    m.sender_user_id, 
                    m.user_message, 
                    m.sent_at, 
                    m.is_read, 
                    u.username, 
                    u.profile_image
                FROM messages m
                JOIN users u ON m.sender_user_id = u.user_id
                WHERE m.conversation_id = ?
                ORDER BY m.sent_at ASC
            ");
            $stmt->bind_param("i", $conversation_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $msgs = $result->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['success' => true, 'messages' => $msgs]);
            exit();
        } else {
            echo json_encode(['success' => false, 'error' => 'Nincs conversation ID']);
            exit();
        }
    }
    
    // Ha ismeretlen AJAX kérés
    exit();
}
// --- AJAX HANDLER VÉGE ---


/* =========================
   1.2 FIX: SAJÁT ADATOK LEKÉRÉSE
========================= */
$stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current_user_data) {
    $current_user_data = ['username' => 'Én', 'profile_image' => ''];
}


/* =========================
   1.5 FIX: ID HELYREÁLLÍTÁS
========================= */
if ($conversation_id > 0 && $product_id === 0) {
    $stmt = $conn->prepare("SELECT product_id FROM conversations WHERE conversation_id = ? LIMIT 1");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $product_id = (int)$row['product_id'];
    }
    $stmt->close();
}

/* =========================
   2. TERMÉK LEKÉRÉS
========================= */
if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.seller_user_id,
        p.product_name,
        p.category,
        p.product_description,
        p.price,
        p.product_status,
        p.pickup_location,
        p.created_at,
        u.username AS seller_username,
        u.profile_image AS seller_image,
        (SELECT image_path FROM images WHERE product_id = p.product_id LIMIT 1) as main_image
    FROM products p
    JOIN users u ON p.seller_user_id = u.user_id
    WHERE p.product_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit();
}

$is_seller = ($product['seller_user_id'] === $user_id);

/* =========================
   3. BESZÉLGETÉS BETÖLTÉS / LÉTREHOZÁS
========================= */
if (!$is_seller && $conversation_id === 0) {

    $stmt = $conn->prepare("
        SELECT conversation_id
        FROM conversations
        WHERE product_id = ?
          AND buyer_user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if ($existing) {
        $conversation_id = (int)$existing['conversation_id'];
    } else {
        $stmt = $conn->prepare("
            INSERT INTO conversations (product_id, seller_user_id, buyer_user_id, conv_status)
            VALUES (?, ?, ?, 'open')
        ");
        $stmt->bind_param(
            "iii",
            $product_id,
            $product['seller_user_id'],
            $user_id
        );
        $stmt->execute();
        $conversation_id = $conn->insert_id;
        $stmt->close();
    }
}

/* =========================
   4. BESZÉLGETÉS ELLENŐRZÉS
========================= */
$stmt = $conn->prepare("
    SELECT 
        c.*,
        p.product_name,
        p.product_status,
        u1.username AS seller_username,
        u2.username AS buyer_username
    FROM conversations c
    JOIN products p ON c.product_id = p.product_id
    JOIN users u1 ON c.seller_user_id = u1.user_id
    JOIN users u2 ON c.buyer_user_id = u2.user_id
    WHERE c.conversation_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
$conversation = $result->fetch_assoc();
$stmt->close();

if (
    !$conversation ||
    !in_array($user_id, [
        $conversation['seller_user_id'],
        $conversation['buyer_user_id']
    ])
) {
    header('Location: products.php');
    exit();
}

/* =========================
  4.2  BESZÉLGETÉS LEZÁRÁSA (ARCHIVÁLÁS)
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['close_conversation']) && 
    $conversation['conv_status'] !== 'archived'
) {
    $stmt = $conn->prepare("UPDATE conversations SET conv_status = 'archived' WHERE conversation_id = ?");
    $stmt->bind_param("i", $conversation_id);
    
    if ($stmt->execute()) {
        $conversation['conv_status'] = 'archived'; // Frissítjük a lokális változót a megjelenítéshez
        $success_message = "A beszélgetést lezártad. További üzenetek küldése nem lehetséges.";
    }
    $stmt->close();
}

/* =========================
   4.5 LÁTTAMOZÁS (HTML BETÖLTÉSKOR IS)
========================= */
$other_user_id = ($conversation['seller_user_id'] === $user_id) ? (int)$conversation['buyer_user_id'] : (int)$conversation['seller_user_id'];

$stmt = $conn->prepare("SELECT user_id, username, profile_image FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $other_user_id);
$stmt->execute();
$other_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("
    UPDATE messages 
    SET is_read = 1 
    WHERE conversation_id = ? 
      AND sender_user_id = ? 
      AND is_read = 0
");
$stmt->bind_param("ii", $conversation_id, $other_user_id);
$stmt->execute();
$stmt->close();

/* =========================
   6. ELADVA GOMB
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['mark_as_sold']) &&
    $is_seller &&
    $conversation['product_status'] === 'active'
) {
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE products SET product_status = 'sold' WHERE product_id = ? AND seller_user_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute(); $stmt->close();

        $stmt = $conn->prepare("UPDATE conversations SET conv_status = 'deal_made' WHERE conversation_id = ?");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute(); $stmt->close();

        $stmt = $conn->prepare("INSERT INTO deals (product_id, seller_user_id, buyer_user_id, conversation_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $product_id, $user_id, $other_user_id, $conversation_id);
        $stmt->execute(); $stmt->close();

        $conn->commit();
        $success_message = "A termék sikeresen eladva.";
        $conversation['product_status'] = 'sold';
        $conversation['conv_status'] = 'deal_made';

    } catch (Throwable $e) {
        $conn->rollback();
        $error_message = "Hiba történt az eladás lezárásakor.";
    }
}

/* =========================
   6.5 ÜZENET KÜLDÉS (PHP FALLBACK HTML FORMHOZ)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_message']) && !isset($_REQUEST['ajax'])) {
    $message_text = trim($_POST['user_message']);
    if (!empty($message_text)) {
        $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_user_id, user_message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $user_id, $message_text);
        if ($stmt->execute()) {
            header("Location: conversation.php?conv_id=" . $conversation_id . "&product_id=" . $product_id);
            exit();
        }
        $stmt->close();
    }
}

/* =========================
   7. ÜZENETEK LEKÉRÉSE (HTML MEGJELENÍTÉSHEZ)
========================= */
$stmt = $conn->prepare("
    SELECT 
        m.message_id,
        m.conversation_id,
        m.sender_user_id,
        m.user_message,
        m.sent_at,
        m.is_read,
        u.username,
        u.profile_image
    FROM messages m
    JOIN users u ON m.sender_user_id = u.user_id
    WHERE m.conversation_id = ?
    ORDER BY m.sent_at ASC
");
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


/* =========================
   9. ÉRTÉKELÉS BEKÜLDÉSE (ÚJ)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    // Csak a vevő értékelhet és csak ha 'deal_made' a státusz
    if (!$is_seller && $conversation['conv_status'] === 'deal_made') {
        
        // Megkeressük a hozzá tartozó deal_id-t
        $stmt = $conn->prepare("SELECT deal_id FROM deals WHERE conversation_id = ? LIMIT 1");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $deal = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($deal) {
            $rating = (int)$_POST['rating'];
            $comment = trim($_POST['review_comment'] ?? '');
            $deal_id = $deal['deal_id'];

            // Ellenőrizzük, értékelt-e már
            $stmt = $conn->prepare("SELECT review_id FROM reviews WHERE deal_id = ? LIMIT 1");
            $stmt->bind_param("i", $deal_id);
            $stmt->execute();
            $already_reviewed = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$already_reviewed) {
                // Beszúrás a reviews táblába
                $stmt = $conn->prepare("INSERT INTO reviews (seller_user_id, buyer_user_id, deal_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiis", $product['seller_user_id'], $user_id, $deal_id, $rating, $comment);
                
                if ($stmt->execute()) {
                    // Opcionális: Eladó átlagának frissítése (avg_rating a users táblában)
                    $conn->query("UPDATE users SET avg_rating = (SELECT AVG(rating) FROM reviews WHERE seller_user_id = {$product['seller_user_id']}) WHERE user_id = {$product['seller_user_id']}");
                    
                    header("Location: conversation.php?conv_id=$conversation_id&success=reviewed");
                    exit();
                }
                $stmt->close();
            }
        }
    }
}

// Ellenőrizzük a megjelenítéshez, hogy van-e már értékelés
$existing_review = null;
if ($conversation['conv_status'] === 'deal_made') {
    $stmt = $conn->prepare("SELECT r.* FROM reviews r JOIN deals d ON r.deal_id = d.deal_id WHERE d.conversation_id = ? LIMIT 1");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $existing_review = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>