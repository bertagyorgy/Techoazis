<?php
session_start();
require_once __DIR__ . '/app/db.php';

/* =========================
   1. AUTH
========================= */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$conversation_id = isset($_GET['conv_id']) ? (int)$_GET['conv_id'] : 0;

/* =========================
   2. TERMÉK LEKÉRÉS
========================= */
/*if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}*/

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
        u.profile_image AS seller_image
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

/*if (!$product) {
    header('Location: products.php');
    exit();
}*/

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
            INSERT INTO conversations (product_id, seller_user_id, buyer_user_id)
            VALUES (?, ?, ?)
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

/*if ($conversation_id <= 0) {
    header('Location: products.php');
    exit();
}*/

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
   5. MÁS FELHASZNÁLÓ
========================= */
$other_user_id = ($conversation['seller_user_id'] === $user_id)
    ? (int)$conversation['buyer_user_id']
    : (int)$conversation['seller_user_id'];
/* =========================
   5/B. MÁS FELHASZNÁLÓ ADATAI
========================= */
$stmt = $conn->prepare("
    SELECT user_id, username, profile_image
    FROM users
    WHERE user_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $other_user_id);
$stmt->execute();
$result = $stmt->get_result();
$other_user = $result->fetch_assoc();
$stmt->close();

if (!$other_user) {
    header('Location: products.php');
    exit();
}

/* =========================
   6. ELADVA GOMB (TRANZAKCIÓ!)
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['mark_as_sold']) &&
    $is_seller &&
    $conversation['product_status'] === 'active'
) {
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("
            UPDATE products
            SET product_status = 'sold'
            WHERE product_id = ?
              AND seller_user_id = ?
        ");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE conversations
            SET status = 'deal_made'
            WHERE conversation_id = ?
        ");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            INSERT INTO deals (product_id, seller_user_id, buyer_user_id, conversation_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiii",
            $product_id,
            $user_id,
            $other_user_id,
            $conversation_id
        );
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $success_message = "A termék sikeresen eladva.";

    } catch (Throwable $e) {
        $conn->rollback();
        $error_message = "Hiba történt az eladás lezárásakor.";
    }
}

/* =========================
   7. ÜZENETEK LEKÉRÉSE
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
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/comments.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="./static/create_post.css">
    <link rel="stylesheet" href="./static/custom_card.css">
    <link rel="stylesheet" href="./static/feature_cards.css">
    <link rel="stylesheet" href="./static/filter_system.css">
    <link rel="stylesheet" href="./static/forum.css">
    <link rel="stylesheet" href="./static/group_view.css">
    <link rel="stylesheet" href="./static/hero_section.css">
    <link rel="stylesheet" href="./static/loading_animation.css">
    <link rel="stylesheet" href="./static/login_page.css">
    <link rel="stylesheet" href="./static/modern_footer.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/post_card.css">
    <link rel="stylesheet" href="./static/profile_pages.css">
    <link rel="stylesheet" href="./static/responsive_adjustments.css">
    <link rel="stylesheet" href="./static/utility_classes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        /* ===============================
          CONVERSATION STYLES
        =============================== */
        .conversation-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .conversation-title h1 {
            color: var(--primary-700);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .conversation-info {
            color: var(--text-light);
            font-size: 1.1rem;
        }
        
        .conversation-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            min-height: 600px;
        }
        
        @media (max-width: 992px) {
            .conversation-layout {
                grid-template-columns: 1fr;
            }
        }
        
        /* ===============================
          CHAT SECTION
        =============================== */
        .chat-section {
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .chat-header {
            padding: 1.5rem;
            background: var(--primary-100);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-info h3 {
            margin: 0;
            color: var(--primary-700);
        }
        
        .user-info p {
            margin: 0.25rem 0 0 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .messages-container {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            max-height: 500px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .message {
            display: flex;
            max-width: 80%;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        
        .message.received {
            align-self: flex-start;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .message-content {
            margin: 0 1rem;
            padding: 1rem;
            border-radius: var(--border-radius-lg);
            position: relative;
            max-width: 100%;
            word-wrap: break-word;
        }
        
        .message.sent .message-content {
            background: var(--accent-200);
            color: var(--accent-800);
            border-bottom-right-radius: 5px;
        }
        
        .message.received .message-content {
            background: var(--primary-100);
            color: var(--text-color);
            border-bottom-left-radius: 5px;
        }
        
        .message-text {
            margin-bottom: 0.5rem;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: var(--text-light);
            text-align: right;
        }
        
        .message-input-container {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            background: var(--surface);
        }
        
        .message-input-form {
            display: flex;
            gap: 1rem;
        }
        
        .message-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-md);
            font-size: 1rem;
            background: var(--background);
            color: var(--text-color);
            resize: none;
            min-height: 60px;
            max-height: 120px;
        }
        
        .message-input:focus {
            outline: none;
            border-color: var(--accent-600);
        }
        
        .send-button {
            padding: 1rem 1.5rem;
            background: linear-gradient(45deg, var(--accent-600), var(--accent-400));
            color: white;
            border: none;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            align-self: flex-end;
        }
        
        .send-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .send-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* ===============================
          PRODUCT SIDEBAR
        =============================== */
        .product-sidebar {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .product-header {
            padding: 1.5rem;
            background: var(--primary-100);
            border-bottom: 1px solid var(--border-color);
        }
        
        .product-header h3 {
            margin: 0 0 1rem 0;
            color: var(--primary-700);
            font-size: 1.25rem;
        }
        
        .product-status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .product-status-badge.active {
            background: var(--success);
            color: white;
        }
        
        .product-status-badge.sold {
            background: var(--danger);
            color: white;
        }
        
        .product-status-badge.hidden {
            background: var(--text-light);
            color: white;
        }
        
        .product-images {
            padding: 1.5rem;
        }
        
        .main-product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--border-radius-md);
            margin-bottom: 1rem;
        }
        
        .product-details {
            padding: 0 1.5rem 1.5rem 1.5rem;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-600);
            margin-bottom: 1rem;
        }
        
        .product-description {
            color: var(--text-color);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .product-meta {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
        }
        
        .meta-item i {
            color: var(--accent-600);
        }
        
        /* ===============================
          DEAL ACTIONS
        =============================== */
        .deal-actions {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .deal-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, var(--success), #34d399);
            color: white;
            border: none;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .deal-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .deal-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .deal-button.sold {
            background: var(--danger);
        }
        
        /* ===============================
          EMPTY STATE
        =============================== */
        .empty-messages {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }
        
        .empty-messages i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }
        
        /* ===============================
          NOTIFICATION BADGE
        =============================== */
        .unread-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--accent-600);
            border-radius: 50%;
            margin-left: 0.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* ===============================
          RESPONSIVE
        =============================== */
        @media (max-width: 768px) {
            .conversation-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .messages-container {
                max-height: 400px;
            }
            
            .message {
                max-width: 90%;
            }
        }
    </style>
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
                <form method="POST" onsubmit="return confirm('Biztosan eladottként szeretnéd jelölni a terméket?');">
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
                                <div class="message <?php echo $message['sender_user_id'] == $user_id ? 'sent' : 'received'; ?>">
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
                                            <?php if ($message['sender_user_id'] == $user_id && $message['is_read']): ?>
                                                <i class="fas fa-check" style="margin-left: 0.5rem; color: var(--accent-600);"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Üzenet küldő -->
                    <div class="message-input-container">
                        <form class="message-input-form" id="message-form">
                            <textarea class="message-input" id="message-input" 
                                      placeholder="Írd ide az üzeneted..." required></textarea>
                            <button type="submit" class="send-button" id="send-button">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
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
                    </div>
                    
                    <div class="product-images">
                        <!-- Itt jönnének a termék képek -->
                        <img src="https://via.placeholder.com/300x200/3b82f6/ffffff?text=Term%C3%A9k+k%C3%A9p" 
                             alt="Termék kép" class="main-product-image">
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
                            <a href="product.php?id=<?php echo $product_id; ?>" class="deal-button">
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
        // Automatikus görgetés az új üzenetekhez
        const messagesContainer = document.getElementById('messages-container');
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Oldal betöltésekor görgetés le
        window.addEventListener('load', scrollToBottom);
        
        // Üzenet küldés AJAX-al
        document.getElementById('message-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();
            const sendButton = document.getElementById('send-button');
            
            if (!message) return;
            
            // Gomb letiltása és ikon változtatás
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // AJAX kérés
            fetch('messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'send',
                    'conversation_id': <?php echo $conversation_id; ?>,
                    'message': message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Üzenet hozzáadása a chathez (frontenden)
                    const messageElement = `
                        <div class="message sent">
                            <div class="message-avatar">
                                <img src="<?php echo $_SESSION['profile_image'] ?? 'images/anonymous.png'; ?>" 
                                     alt="<?php echo $_SESSION['username']; ?>">
                            </div>
                            <div class="message-content">
                                <div class="message-text">
                                    ${message.replace(/\n/g, '<br>')}
                                </div>
                                <div class="message-time">
                                    ${new Date().toLocaleTimeString('hu-HU', {hour: '2-digit', minute:'2-digit'})}
                                    <i class="fas fa-check" style="margin-left: 0.5rem; color: var(--accent-600);"></i>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Ha üres volt a chat, eltüntetjük az üres állapotot
                    const emptyState = document.querySelector('.empty-messages');
                    if (emptyState) {
                        emptyState.remove();
                    }
                    
                    // Üzenet hozzáadása
                    messagesContainer.insertAdjacentHTML('beforeend', messageElement);
                    
                    // Input ürítése
                    messageInput.value = '';
                    
                    // Görgetés az új üzenethez
                    scrollToBottom();
                } else {
                    alert('Hiba az üzenet küldésekor: ' + (data.error || 'Ismeretlen hiba'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hálózati hiba történt. Próbáld újra!');
            })
            .finally(() => {
                // Gomb visszaállítása
                sendButton.disabled = false;
                sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
            });
        });
        
        // Automatikus üzenet frissítés (minden 5 másodpercben)
        let lastMessageId = <?php echo !empty($messages) ? end($messages)['message_id'] : 0; ?>;
        
        function fetchNewMessages() {
            fetch(`messages.php?action=get&conversation_id=<?php echo $conversation_id; ?>&last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            // Csak az új üzeneteket adjuk hozzá
                            if (message.message_id > lastMessageId) {
                                const isSent = message.sender_user_id == <?php echo $user_id; ?>;
                                const messageElement = `
                                    <div class="message ${isSent ? 'sent' : 'received'}">
                                        <div class="message-avatar">
                                            <img src="${message.profile_image}" 
                                                 alt="${message.username}">
                                        </div>
                                        <div class="message-content">
                                            <div class="message-text">
                                                ${message.user_message.replace(/\n/g, '<br>')}
                                            </div>
                                            <div class="message-time">
                                                ${new Date(message.sent_at).toLocaleTimeString('hu-HU', {hour: '2-digit', minute:'2-digit'})}
                                                ${isSent ? '<i class="fas fa-check" style="margin-left: 0.5rem; color: var(--accent-600);"></i>' : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;
                                
                                // Ha üres volt a chat, eltüntetjük az üres állapotot
                                const emptyState = document.querySelector('.empty-messages');
                                if (emptyState) {
                                    emptyState.remove();
                                }
                                
                                messagesContainer.insertAdjacentHTML('beforeend', messageElement);
                                lastMessageId = message.message_id;
                            }
                        });
                        
                        // Ha volt új üzenet, görgetünk le
                        if (data.messages.length > 0) {
                            scrollToBottom();
                        }
                    }
                })
                .catch(error => console.error('Error fetching messages:', error));
        }
        
        // Frissítés indítása (minden 5 másodpercben)
        setInterval(fetchNewMessages, 5000);
        
        // Input automatikus magasítás
        const textarea = document.getElementById('message-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>