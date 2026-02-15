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
            // sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // JAVÍTÁS: A PHP által várt paraméterek (conv_id, user_message) és AJAX jelző használata
            fetch('conversation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    'ajax': '1', // Jelezzük a PHP-nak, hogy JSON-t kérünk
                    'action': 'send',
                    'conv_id': conversationId, // JAVÍTVA: conversation_id helyett conv_id
                    'user_message': messageText // JAVÍTVA: message helyett user_message
                })
            })
            .then(response => {
                // Megpróbáljuk JSON-ként olvasni, ha nem megy, akkor szövegként (debug)
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("Szerver válasz nem JSON:", text);
                        throw new Error("A szerver nem JSON formátumban válaszolt. Ellenőrizd a conversation.php fájlt.");
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    const emptyState = document.querySelector('.empty-messages');
                    if (emptyState) emptyState.remove();

                    // Ideiglenes üzenet objektum a megjelenítéshez
                    const tempMessage = {
                        message_id: data.message_id || Date.now(), 
                        user_message: messageText,
                        profile_image: currentUserProfileImage,
                        username: currentUserName,
                        sent_at: new Date(),
                        sender_user_id: currentUserId
                    };

                    // Küldéskor alapból 1 szürke pipa (nem olvasott)
                    const messageElement = createMessageHTML(tempMessage, true, false);
                    
                    messagesContainer.insertAdjacentHTML('beforeend', messageElement);

                    if (data.message_id) {
                        lastMessageId = data.message_id;
                    }
                    
                    messageInput.value = '';
                    if (messageInput.style) messageInput.style.height = 'auto';
                    scrollToBottom();
                } else {
                    alert('Hiba: ' + (data.error || 'Ismeretlen hiba'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Csak akkor szólunk, ha tényleg baj van, nem minden fetch hibánál
                // alert('Hálózati hiba: ' + error.message);
            })
            .finally(() => {
                sendButton.disabled = false;
                sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
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

        // 1. LÉPÉS: Jelezzük a szervernek, hogy itt vagyunk (olvasottá tétel)
        markMessagesAsRead();

        // 2. LÉPÉS: Lekérjük az adatokat JSON formátumban
        const timestamp = new Date().getTime();

        // JAVÍTVA: paraméterek (conv_id, ajax=1)
        fetch(`conversation?action=get_messages&conv_id=${conversationId}&last_id=0&t=${timestamp}&ajax=1`)
            .then(response => {
                 return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        // Ha HTML jön vissza (pl. a teljes oldal), azt csendben ignoráljuk,
                        // mert akkor még nincs kész a PHP oldal az AJAX fogadására.
                        return { success: false, error: "Not JSON" };
                    }
                });
            })
            .then(data => {
                if (data.success && data.messages) {
                    let shouldScroll = false;
                    
                    data.messages.forEach(message => {
                        // 1. Megkeressük, létezik-e már az üzenet a DOM-ban
                        const existingElement = document.querySelector(`.message[data-message-id="${message.message_id}"]`);
                        const isSentByMe = message.sender_user_id == currentUserId;

                        if (existingElement) {
                            // HA LÉTEZIK: Ellenőrizzük a státuszát (csak ha én küldtem)
                            if (isSentByMe && message.is_read == 1) {
                                // Megkeressük a benne lévő ikont
                                const icon = existingElement.querySelector('.message-status-icon');
                                
                                // Ha még "sent" (szürke pipa) állapotban van, frissítjük "read"-re (dupla színes pipa)
                                if (icon && icon.classList.contains('sent')) {
                                    icon.className = 'fas fa-check-double message-status-icon read';
                                    icon.title = 'Látta';
                                    icon.style.color = 'var(--accent-600)'; 
                                }
                            }
                        } else {
                            // HA NEM LÉTEZIK (Új üzenet): Hozzáadjuk a listához
                            if (message.message_id > lastMessageId) {
                                const isRead = isSentByMe && (message.is_read == 1);
                                const html = createMessageHTML(message, isSentByMe, isRead);
                                
                                const emptyState = document.querySelector('.empty-messages');
                                if (emptyState) emptyState.remove();

                                messagesContainer.insertAdjacentHTML('beforeend', html);
                                lastMessageId = message.message_id;
                                shouldScroll = true;
                            }
                        }
                    });
                    
                    if (shouldScroll) scrollToBottom();
                }
            })
            .catch(error => console.error('Error fetching messages:', error));
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