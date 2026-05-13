document.addEventListener('DOMContentLoaded', function() {
    
    // Config változók
    // JAVÍTÁS: Ellenőrizzük, hogy léteznek-e az értékek
    if (typeof chatConfig === 'undefined') return;

    const baseUrl = chatConfig.baseUrl; // Most már elérhető
    const conversationId = chatConfig.conversationId;
    const currentUserId = chatConfig.userId;
    const currentUserProfileImage = chatConfig.profileImage;
    const currentUserName = chatConfig.username;
    let lastMessageId = chatConfig.lastMessageId;

    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');

    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    // Kezdeti görgetés
    scrollToBottom();

    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Segédfüggvény: Üzenet HTML generálása
    function createMessageHTML(message, isSentByMe, isRead = false) {
        // Idő formázása
        const timeString = new Date(message.sent_at || new Date()).toLocaleTimeString('hu-HU', {hour: '2-digit', minute:'2-digit'});
        
        // Státusz ikon logika
        let statusIcon = '';
        if (isSentByMe) {
            if (isRead) {
                statusIcon = '<i class="fas fa-check-double message-status-icon read" title="Látta" style="margin-left: 0.5rem; color: var(--accent-600);"></i>';
            } else {
                statusIcon = '<i class="fas fa-check message-status-icon sent" title="Elküldve" style="margin-left: 0.5rem; color: #aaa;"></i>';
            }
        }

        // --- JAVÍTOTT KÉP LOGIKA ---
        // Megvizsgáljuk, hogy az URL már tartalmazza-e a http-t vagy a baseUrl-t
        let avatarUrl = message.profile_image;
        
        // Ha nincs kép, legyen egy alapértelmezett (opcionális biztonsági lépés)
        if (!avatarUrl) {
            avatarUrl = `${baseUrl}/images/anonymous.png`; 
        } 
        // Ha nem kezdődik http-vel, akkor elé rakjuk a baseUrl-t
        else if (!avatarUrl.startsWith('http')) {
            // Vigyázunk, hogy ne legyen duplaper (//) az összefűzésnél, ha a baseUrl végén vagy a kép elején lenne
            const cleanBase = baseUrl.replace(/\/$/, '');
            const cleanPath = avatarUrl.replace(/^\//, '');
            avatarUrl = `${cleanBase}/${cleanPath}`;
        }

        return `
            <div class="message ${isSentByMe ? 'sent' : 'received'}" data-message-id="${message.message_id}">
                <div class="message-avatar">
                    <img src="${avatarUrl}" alt="${message.username}">
                </div>
                <div class="message-content">
                    <div class="message-text">
                        ${message.user_message.replace(/\n/g, '<br>')}
                    </div>
                    <div class="message-time">
                        ${timeString}
                        ${statusIcon}
                    </div>
                </div>
            </div>
        `;
    }

    if (messageInput) { 
        messageInput.addEventListener('keydown', function(e) {
             if (e.key === 'Enter' && !e.shiftKey) { 
                e.preventDefault(); 
                messageForm.requestSubmit(); 
            } 
        }); 
    }

    // Üzenetküldés
    if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                const messageText = messageInput.value.trim();
                if (!messageText) { e.preventDefault(); return; }
                e.preventDefault();
                
                sendButton.disabled = true;
                
                fetch('conversation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        'ajax': '1',
                        'action': 'send',
                        'conv_id': conversationId,
                        'user_message': messageText
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Hálózati hiba');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        messageInput.style.height = 'auto';

                        if (!document.querySelector(`[data-message-id="${data.message_id}"]`)) {
                            const tempMessage = {
                                message_id: data.message_id, 
                                user_message: messageText,
                                sent_at: new Date().toISOString(),
                                sender_user_id: currentUserId,
                                // Pótold ezeket a sorokat:
                                username: currentUserName,
                                profile_image: currentUserProfileImage
                            };
                            
                            // Így a createMessageHTML már tudni fogja, mit tegyen az <img> tagbe
                            const html = createMessageHTML(tempMessage, true, false);
                            messagesContainer.insertAdjacentHTML('beforeend', html);
                        }
                        
                        lastMessageId = data.message_id;
                        scrollToBottom();
                    }
                })
                .catch(error => {
                    console.error('Fetch hiba (valószínűleg a PHP header() miatt):', error);
                    // Ha hiba van, akkor is ürítheted, vagy értesítheted a felhasználót
                })
                .finally(() => { 
                    sendButton.disabled = false; 
                });
            });
        }
    

    // ÚJ FÜGGVÉNY: Jelzi a szervernek, hogy épp nézzük a beszélgetést
    function markMessagesAsRead() {
        if (!conversationId) return;

        const pageUrl = 'conversation'; 
        const timestamp = new Date().getTime();

        // A 'ping=1' és 'ajax=1' paraméterrel jelezzük a PHP-nak
        fetch(`${pageUrl}?conv_id=${conversationId}&t=${timestamp}&ping=1&ajax=1`, { method: 'GET' })
            .catch(err => console.error("Nem sikerült a láttamozás küldése:", err));
    }

    // Üzenetek ÉS státuszok frissítése (Polling)
    function fetchNewMessages() {
        if (!conversationId) return;
        markMessagesAsRead();

        const timestamp = new Date().getTime();
        fetch(`conversation?action=get_messages&conv_id=${conversationId}&ajax=1&t=${timestamp}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages) {
                    let shouldScroll = false;
                    
                    data.messages.forEach(message => {
                        const msgId = parseInt(message.message_id);
                        const isSentByMe = parseInt(message.sender_user_id) === parseInt(currentUserId);
                        
                        // 1. Megkeressük, létezik-e már az üzenet a DOM-ban
                        const existingElement = document.querySelector(`.message[data-message-id="${msgId}"]`);

                        if (existingElement) {
                            // HA LÉTEZIK: Ellenőrizzük a státuszát (csak ha én küldtem)
                            if (isSentByMe && message.is_read == 1) {
                                const icon = existingElement.querySelector('.message-status-icon');
                                
                                // Ha még "sent" (szürke pipa), frissítjük "read"-re (dupla pipa)
                                if (icon && icon.classList.contains('sent')) {
                                    icon.className = 'fas fa-check-double message-status-icon read';
                                    icon.title = 'Látta';
                                    icon.style.color = 'var(--accent-600)'; 
                                }
                            }
                        } else {
                            // HA NEM LÉTEZIK: Ellenőrizzük, hogy tényleg újabb-e, és betesszük a képernyőre
                            if (msgId > parseInt(lastMessageId)) {
                                const html = createMessageHTML(message, isSentByMe, message.is_read == 1);
                                messagesContainer.insertAdjacentHTML('beforeend', html);
                                shouldScroll = true;
                                lastMessageId = msgId; // Frissítjük a követést
                            }
                        }
                    });
                    if (shouldScroll) scrollToBottom();
                }
            });
    }

    // HIBAJAVÍTÁS: Meglévő üzenetek pipáinak pótlása betöltéskor
    function fixMissingIconsOnLoad() {
        const sentMessages = document.querySelectorAll('.message.sent .message-time');
        
        sentMessages.forEach(timeBox => {
            const hasAnyIcon = timeBox.querySelector('i.fa-check') || timeBox.querySelector('i.fa-check-double');

            if (!hasAnyIcon) {
                const icon = document.createElement('i');
                icon.className = 'fas fa-check message-status-icon sent';
                icon.title = 'Elküldve';
                icon.style.marginLeft = '0.5rem';
                icon.style.color = '#aaa';
                timeBox.appendChild(icon);
            } else if (hasAnyIcon && !hasAnyIcon.classList.contains('message-status-icon')) {
                 hasAnyIcon.classList.add('message-status-icon');
                 if (hasAnyIcon.classList.contains('fa-check-double')) {
                     hasAnyIcon.classList.add('read');
                 } else {
                     hasAnyIcon.classList.add('sent');
                 }
            }
        });
    }

    fixMissingIconsOnLoad();
    setInterval(fetchNewMessages, 3000); 
});