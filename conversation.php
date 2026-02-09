<?php
// conversation.php - A tiszta megjelenítő fájl
require_once __DIR__ . '/config.php';
require_once ROOT_PATH . '/conversation_logic.php';

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | Beszélgetés</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/converstation.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="<?= BASE_URL ?>/assets/js/converstation.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
</head>
<body>
    <?php include ROOT_PATH . '/views/navbar.php'; ?>
    
    <section class="section-padding">
        <div class="conversation-container">
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
            
            <?php if (isset($_GET['success']) && $_GET['success'] === 'reviewed'): ?>
                <div style="background: var(--success); color: white; padding: 1rem; border-radius: var(--border-radius-md); margin-bottom: 2rem; text-align: center;">
                    <i class="fas fa-check-circle"></i> Köszönjük az értékelést!
                </div>
            <?php endif; ?>
            
            <div class="conversation-layout">
                <div class="chat-section">
                    <div class="chat-header">
                        <div class="user-avatar">
                            <img src="<?php echo htmlspecialchars($other_user['profile_image'] ?? 'images/anonymous.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($other_user['username']); ?>">
                        </div>
                        <div class="user-info">
                            <h3><?php echo htmlspecialchars($other_user['username']); ?></h3>
                            <p>
                                <span class="product-status-badge <?php echo $conversation['product_status']; ?>">
                                    <?php 
                                    $status_map = ['active' => 'Aktív', 'sold' => 'Eladva', 'hidden' => 'Rejtett'];
                                    echo $status_map[$conversation['product_status']] ?? $conversation['product_status'];
                                    ?>
                                </span>
                                • <?php echo $is_seller ? 'Vevő' : 'Eladó'; ?>
                            </p>
                        </div>
                    </div>
                    
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
                                        <img src="<?php echo htmlspecialchars($message['profile_image']); ?>" alt="Avatar">
                                    </div>
                                    <div class="message-content">
                                        <div class="message-text"><?php echo nl2br(htmlspecialchars($message['user_message'])); ?></div>
                                        <div class="message-time">
                                            <?php echo date('H:i', strtotime($message['sent_at'])); ?>
                                            <?php if ($message['sender_user_id'] == $user_id): ?>
                                                <?php if ($message['is_read']): ?>
                                                    <i class="fas fa-check-double message-status-icon read" title="Látta" style="margin-left: 0.5rem; color: var(--accent-600);"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-check message-status-icon sent" title="Elküldve" style="margin-left: 0.5rem; color: #aaa;"></i>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($conversation['conv_status'] === 'open'): ?>
                        
                        <div class="message-input-container">
                            <form class="message-input-form" id="message-form" method="POST" action="<?= BASE_URL ?>/conversation.php?conv_id=<?php echo $conversation_id; ?>&product_id=<?php echo $product_id; ?>">
                                <textarea class="message-input" id="message-input" name="user_message" placeholder="Írd ide az üzeneted..." required></textarea>
                                <button type="submit" class="send-button" id="send-button">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>

                    <?php elseif ($conversation['conv_status'] === 'deal_made'): ?>
                        
                        <div class="message-input-container" style="display: block; text-align: center; padding: 1.5rem; background: #f8f9fa; border-top: 1px solid #ddd;">
                            <p style="font-weight: bold; color: var(--success, #28a745); margin-bottom: 1rem;">
                                <i class="fas fa-handshake"></i> Az üzlet sikeresen lezárult!
                            </p>

                            <?php if (!$is_seller): ?>
                                <?php if (!$existing_review): ?>
                                    <form method="POST" style="max-width: 400px; margin: 0 auto;">
                                        <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">Értékeld az eladót:</p>
                                        <select name="rating" required style="width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #ccc;">
                                            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                            <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                            <option value="3">⭐⭐⭐ (3/5)</option>
                                            <option value="2">⭐⭐ (2/5)</option>
                                            <option value="1">⭐ (1/5)</option>
                                        </select>
                                        <textarea name="review_comment" placeholder="Írj pár szót az adásvételről..." style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical; min-height: 60px;"></textarea>
                                        <button type="submit" name="submit_review" class="deal-button" style="width: 100%; background: var(--accent);">
                                            Értékelés beküldése
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p style="color: var(--primary); font-style: italic;">
                                        <i class="fas fa-star"></i> Már értékelted ezt az üzletet: <strong><?php echo $existing_review['rating']; ?>/5</strong>
                                    </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p>Várjuk a vevő értékelését.</p>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        
                        <div class="message-input-container" style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-top: 1px solid #ddd; color: #666;">
                            <p style="font-weight: bold;"><i class="fas fa-lock"></i> A beszélgetés lezárult.</p>
                            <p style="margin-top: 0.5rem; font-size: 0.9em;">További üzenetek nem küldhetőek.</p>
                        </div>

                    <?php endif; ?>
                </div>
                
                <div class="product-sidebar">
                    <div class="product-header">
                        <h3>Termék információk</h3>
                        <div class="product-status-badge <?php echo $conversation['product_status']; ?>">
                            <?php echo $status_map[$conversation['product_status']] ?? $conversation['product_status']; ?>
                        </div>
                        
                        <form method="POST" action="<?= BASE_URL ?>/conversation.php?conv_id=<?php echo $conversation_id; ?>&product_id=<?php echo $product_id; ?>" 
                              style="display: inline-block; margin-left: 10px;">
                            
                            <button type="submit" name="close_conversation" class="deal-button" 
                                    style="background-color: var(--error, #dc3545); margin-bottom: 5px;"
                                    onclick="return confirm('Biztosan le akarod zárni a beszélgetést?');"
                                    <?php echo $conversation['conv_status'] !== 'open' ? 'disabled' : ''; ?>>
                                <i class="fas fa-lock"></i>
                                <?php echo $conversation['conv_status'] === 'archived' ? 'Lezárva' : 'Lezárás'; ?>
                            </button>

                            <?php if ($conversation['conv_status'] === 'open' && $conversation['product_status'] === 'active'): ?>
                                
                                <?php 
                                    // Megnézzük, hogy ÉN (a bejelentkezett user) megnyomtam-e már
                                    $i_have_agreed = ($is_seller && $conversation['is_seller_agreed']) || (!$is_seller && $conversation['is_buyer_agreed']);
                                    
                                    // Megnézzük, hogy a MÁSIK fél megnyomta-e már
                                    $other_has_agreed = ($is_seller && $conversation['is_buyer_agreed']) || (!$is_seller && $conversation['is_seller_agreed']);
                                ?>

                                <?php if ($i_have_agreed): ?>
                                    <button type="button" class="deal-button" disabled style="background-color: #f0ad4e; cursor: not-allowed; opacity: 0.8;">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo $other_has_agreed ? 'Feldolgozás...' : 'Várakozás a partnerre...'; ?>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="mark_as_sold" class="deal-button" onclick="return confirm('Megerősíted, hogy megállapodtatok az üzletben? Ha a másik fél is rányom, a termék eladottá válik.');">
                                        <i class="fas fa-handshake"></i> Megállapodtunk
                                        <?php if ($other_has_agreed): ?>
                                            <span style="font-size: 0.8em; display: block;">(A partner már jóváhagyta!)</span>
                                        <?php endif; ?>
                                    </button>
                                <?php endif; ?>

                            <?php elseif ($conversation['conv_status'] === 'deal_made'): ?>
                                <button type="button" class="deal-button sold" disabled>
                                    <i class="fas fa-check-circle"></i> Üzlet megkötve
                                </button>
                            <?php endif; ?>

                        </form>
                    </div>
                    
                    <div class="product-images">
                        <img src="<?php echo htmlspecialchars($product['main_image'] ?? BASE_URL . '/uploads/products/default_product.png'); ?>"    
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="main-product-image">
                    </div>
                    
                    <div class="product-details">
                        <div class="product-price">
                            <?php echo $product['price'] ? number_format($product['price'], 0, ',', ' ') . ' Ft' : 'Alkuképes'; ?>
                        </div>
                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
                        </div>
                        <div class="product-meta">
                            <div class="meta-item"><i class="fas fa-tag"></i> Kategória: <?php echo htmlspecialchars($product['category']); ?></div>
                            <div class="meta-item"><i class="fas fa-map-marker-alt"></i> Átvétel: <?php echo htmlspecialchars($product['pickup_location'] ?? '-'); ?></div>
                            <div class="meta-item"><i class="fas fa-calendar"></i> Feladva: <?php echo date('Y. m. d.', strtotime($product['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        const chatConfig = {
            conversationId: <?php echo json_encode($conversation_id); ?>,
            userId: <?php echo json_encode($user_id); ?>,
            profileImage: <?php echo json_encode($current_user_data['profile_image']); ?>,
            username: <?php echo json_encode($current_user_data['username']); ?>,
            lastMessageId: <?php echo !empty($messages) ? end($messages)['message_id'] : 0; ?>
        };
    </script>
</body>
</html>