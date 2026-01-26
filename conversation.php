<?php
// conversation.php - A tiszta megjelenítő fájl
require_once 'conversation_logic.php'; 
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
                    <?php if ($conversation['conv_status'] === 'open'): ?>
                        
                        <div class="message-input-container">
                            <form class="message-input-form" id="message-form" method="POST">
                                <textarea class="message-input" id="message-input" name="user_message" placeholder="Írd ide az üzeneted..." required></textarea>
                                <button type="submit" class="send-button" id="send-button">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>

                    <?php else: ?>

                        <div class="message-input-container" style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-top: 1px solid #ddd; color: #666;">
                            
                            <?php if ($conversation['conv_status'] === 'deal_made'): ?>
                                <p style="font-weight: bold; color: var(--success, #28a745);">
                                    <i class="fas fa-handshake"></i> Szuper! Az üzlet megköttetett.
                                </p>
                            <?php else: ?>
                                <p style="font-weight: bold; color: var(--text-muted, #6c757d);">
                                    <i class="fas fa-lock"></i> A beszélgetés lezárult.
                                </p>
                            <?php endif; ?>
                            
                            <p style="margin-top: 0.5rem; font-size: 0.9em;">További üzenetek nem küldhetőek ebben a beszélgetésben.</p>
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
                                <?php if ($is_seller): ?>
                                <button type="submit" name="mark_as_sold" class="deal-button <?php echo $conversation['conv_status'] === 'deal_made' ? 'sold' : ''; ?>"
                                    <?php echo $conversation['conv_status'] === 'deal_made' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-handshake"></i>
                                    <?php echo $conversation['conv_status'] === 'deal_made' ? 'Megállapodva' : 'Megállapodtunk'; ?>
                                </button>
                                <?php endif; ?>
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