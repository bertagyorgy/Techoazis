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
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beszélgetés | Techoázis</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/converstation.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/converstation.js" defer></script>

</head>
<body>
    <?php include './views/navbar.php'; ?>
    
    <section class="section-padding">
        <div class="conversation-container">
            <!-- Fejléc -->
            <div class="conversation-header">
                <div class="conversation-title">
                    <h1>Beszélgetés</h1>
                    <div class="conversation-info">
                        <i class="fas fa-comments"></i>
                        <?php echo htmlspecialchars($conversation['product_name']); ?> • 
                        <?php echo htmlspecialchars($other_user['username']); ?>
                    </div>
                </div>
                
                <?php if ($is_seller): ?>
                <form method="POST" action="conversation.php?conv_id=<?php echo $conversation_id; ?>&product_id=<?php echo $product_id; ?>" onsubmit="return confirm('Biztosan eladottként szeretnéd jelölni a terméket?');">
                    <button type="submit" name="mark_as_sold" class="deal-button <?php echo $conversation['conv_status'] === 'deal_made' ? 'sold' : ''; ?>"
                            <?php echo $conversation['conv_status'] === 'deal_made' ? 'disabled' : ''; ?>>
                        <i class="fas fa-handshake"></i>
                        <?php echo $conversation['conv_status'] === 'deal_made' ? 'Megállapodva' : 'Megállapodtunk'; ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <?php if (isset($success_message)): ?>
            <div style="background: var(--success); color: white; padding: 1rem; border-radius: var(--border-radius-md); margin-bottom: 2rem; text-align: center;">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Fő tartalom -->
            <div class="conversation-layout">
                <!-- Bal oldal: Chat -->
                <div class="chat-section">
                    <div class="chat-header">
                        <div class="user-avatar">
                            <img src="<?php echo htmlspecialchars($product['seller_image'] ?? 'images/anonymous.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($other_user['username']); ?>">
                        </div>
                        <div class="user-info">
                            <h3><?php echo htmlspecialchars($other_user['username']); ?></h3>
                            <p>
                                <span class="product-status-badge <?php echo $conversation['product_status']; ?>">
                                    <?php 
                                    $status_text = [
                                        'active' => 'Aktív',
                                        'sold' => 'Eladva',
                                        'hidden' => 'Rejtett'
                                    ];
                                    echo $status_text[$conversation['product_status']] ?? $conversation['product_status'];
                                    ?>
                                </span>
                                • <?php echo $is_seller ? 'Vevő' : 'Eladó'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Üzenetek -->
                    <div class="messages-container" id="messages-container">
                        <?php if (empty($messages)): ?>
                            <div class="empty-messages">
                                <i class="fas fa-comment-slash"></i>
                                <h3>Még nincsenek üzenetek</h3>
                                <p>Legyél te az első, aki üzenetet küld!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo $message['sender_user_id'] == $user_id ? 'sent' : 'received'; ?>" data-message-id="<?php echo $message['message_id']; ?>">
                                    <div class="message-avatar">
                                        <img src="<?php echo htmlspecialchars($message['profile_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($message['username']); ?>">
                                    </div>
                                    <div class="message-content">
                                        <div class="message-text">
                                            <?php echo nl2br(htmlspecialchars($message['user_message'])); ?>
                                        </div>
                                        <div class="message-time">
                                            <?php echo date('H:i', strtotime($message['sent_at'])); ?>
                                            <?php if ($message['sender_user_id'] == $user_id): ?>
                                                <?php if ($message['is_read']): ?>
                                                    <!-- OLVASOTT: Dupla színes pipa -->
                                                    <i class="fas fa-check-double message-status-icon read" title="Látta" style="margin-left: 0.5rem; color: var(--accent-600);"></i>
                                                <?php else: ?>
                                                    <!-- NEM OLVASOTT: Szürke pipa -->
                                                    <i class="fas fa-check message-status-icon sent" title="Elküldve" style="margin-left: 0.5rem; color: #aaa;"></i>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Üzenet küldő -->
                    <?php if ($conversation['conv_status'] !== 'archived' && $conversation['conv_status'] !== 'deal_made'): ?>
                        <div class="message-input-container">
                            <form class="message-input-form" id="message-form" action="..." method="POST">
                                <textarea class="message-input" id="message-input" name="user_message" placeholder="Írd ide az üzeneted..." required></textarea>
                                <button type="submit" class="send-button" id="send-button">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="message-input-container" style="text-align: center; padding: 1rem; background: #f8f9fa; border-top: 1px solid #ddd;">
                            <p><i class="fas fa-info-circle"></i> Ez a beszélgetés lezárult, további üzenetek nem küldhetőek.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Jobb oldal: Termék információk -->
                <div class="product-sidebar">
                    <div class="product-header">
                        <h3>Termék információk</h3>
                        <div class="product-status-badge <?php echo $conversation['product_status']; ?>">
                            <?php 
                            echo $status_text[$conversation['product_status']] ?? $conversation['product_status'];
                            ?>
                        </div>
                            <form method="POST" action="conversation.php?conv_id=<?php echo $conversation_id; ?>&product_id=<?php echo $product_id; ?>" 
                                onsubmit="return confirm('Biztosan le akarod zárni a beszélgetést? Több üzenetet nem tudtok váltani.');" 
                                style="display: inline-block; margin-left: 10px;">
                                
                                <button type="submit" name="close_conversation" class="deal-button" 
                                        style="background-color: var(--error, #dc3545);"
                                        <?php echo $conversation['conv_status'] === 'archived' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-lock"></i>
                                    <?php echo $conversation['conv_status'] === 'archived' ? 'Lezárva' : 'Beszélgetés lezárása'; ?>
                                </button>
                            </form>
                    </div>
                    
                    <div class="product-images">
                        <?php if (!empty($product['main_image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['main_image']); ?>" 
                                alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                class="main-product-image">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200/3b82f6/ffffff?text=Nincs+kép" 
                                alt="Nincs elérhető kép" 
                                class="main-product-image">
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-details">
                        <div class="product-price">
                            <?php 
                            if ($product['price']) {
                                echo number_format($product['price'], 0, ',', ' ') . ' Ft';
                            } else {
                                echo 'Alkuképes';
                            }
                            ?>
                        </div>
                        
                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
                        </div>
                        
                        <div class="product-meta">
                            <div class="meta-item">
                                <i class="fas fa-tag"></i>
                                <span>Kategória: <?php echo htmlspecialchars($product['category']); ?></span>
                            </div>
                            
                            <?php if ($product['pickup_location']): ?>
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Átvétel: <?php echo htmlspecialchars($product['pickup_location']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span>Feladva: <?php echo date('Y. m. d.', strtotime($product['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="deal-actions">
                        <?php if (!$is_seller): ?>
                            <a href="product_detail.php?id=<?php echo $product_id; ?>" class="deal-button">
                                <i class="fas fa-external-link-alt"></i>
                                Termék oldala
                            </a>
                        <?php else: ?>
                            <a href="edit_product.php?id=<?php echo $product_id; ?>" class="deal-button" style="background: var(--primary-500);">
                                <i class="fas fa-edit"></i>
                                Termék szerkesztése
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        const chatConfig = {
            conversationId: <?php echo json_encode($conversation_id); ?>,
            userId: <?php echo json_encode($user_id); ?>,
            profileImage: <?php echo json_encode(!empty($current_user_data['profile_image']) ? $current_user_data['profile_image'] : 'images/anonymous.png'); ?>,
            username: <?php echo json_encode($current_user_data['username']); ?>,
            lastMessageId: <?php echo !empty($messages) ? end($messages)['message_id'] : 0; ?>
        };
    </script>
</body>
</html>