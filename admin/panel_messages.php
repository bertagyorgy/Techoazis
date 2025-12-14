<?php
require '../app/auth_check.php';

// --- ÜZENETEK KONFIGURÁCIÓJA ---
$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'messages',
    'pk' => 'message_id',
    'page_file' => '../admin/panel_messages.php',
    'page_title' => 'Beszélgetés Üzenetek',
    'singular_name' => 'üzenet',

    // --- LISTÁZÁS KONFIGURÁCIÓ (JOIN-nal a küldőhöz és a beszélgetéshez) ---
    'list_columns' => [
        'message_id' => 'ID',
        'conversation_summary' => 'Beszélgetés', // JOIN-nal töltjük be
        'sender_username' => 'Küldő',          // JOIN-nal töltjük be
        'user_message' => 'Üzenet',
        'sent_at' => 'Elküldve',
        'is_read' => 'Olvasott?'
    ],
    
    // JOIN-ok a küldő felhasználónévhez (users) és a beszélgetés összefoglalójához (conversations)
    // Megjegyzés: A conversation_summary-t a products és users táblák JOIN-olásával hozzuk létre
    'list_query' => "SELECT m.*, 
                            u.username AS sender_username, 
                            CONCAT(p.product_name, ' (Vevő: ', b.username, ')') AS conversation_summary -- Összefoglaló a listához
                     FROM messages m
                     JOIN users u ON m.sender_user_id = u.user_id
                     JOIN conversations c ON m.conversation_id = c.conversation_id
                     JOIN products p ON c.product_id = p.product_id
                     JOIN users b ON c.buyer_user_id = b.user_id -- A vevő neve az összefoglalóhoz
                     ORDER BY m.sent_at DESC",

    // Formázók
    'list_formatters' => [
        // Rövidítés a lista nézetben (első 50 karakter)
        'user_message' => function ($value, $row) {
            return strlen($value) > 50 ? htmlspecialchars(substr($value, 0, 50)) . '...' : htmlspecialchars($value);
        },
        // Logikai mező formázása
        'is_read' => function ($value, $row) {
            return $value ? '✅ Igen' : '❌ Nem';
        }
    ],
    
    // --- ŰRLAP KONFIGURÁCIÓ ---
    // Az 'is_read' mezőt is kezelni kell a formon, ha azt adminisztrátorként szerkeszteni szeretnénk
    'form_fields' => ['conversation_id', 'sender_user_id', 'user_message', 'is_read'],

    'fields' => [
        'message_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        // Beszélgetés kiválasztása
        'conversation_id' => [
            'label' => 'Beszélgetés', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            // Itt a 'display_col' a JOIN-nal létrehozott mezőre hivatkozik, ami nem a foreign_key táblában van,
            // ezért a 'foreign_key' csak az ID és a megjelenítéshez szükséges. 
            // Megjegyzés: A generikus CRUD-nak tudnia kell kezelni a dinamikus megjelenítő oszlopokat, ha a list_query alapján tölti be az opciókat.
            // Egyszerűbb és stabilabb megoldás, ha a conversation_id alapján választunk ki valamit a formon:
            'foreign_key' => [
                'table' => 'conversations', 
                'value_col' => 'conversation_id', 
                // Mivel a conversations táblában nincs egyszerű megjelenítendő oszlop, 
                // a 'display_col' itt lehetne a 'conversation_id' vagy 'created_at'.
                'display_col' => 'conversation_id' // Ezt lehet, hogy manuálisan kell felülírni a formnál
            ]
        ],
        
        // Küldő felhasználó kiválasztása
        'sender_user_id' => [
            'label' => 'Küldő felhasználó', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'users', 'value_col' => 'user_id', 'display_col' => 'username']
        ],
        
        // Üzenet tartalma
        'user_message' => [
            'label' => 'Üzenet', 
            'type' => 'textarea', 
            'required' => true, 
            'param_type' => 's'
        ],

        // Elküldve (csak listázáshoz)
        'sent_at' => ['label' => 'Elküldve', 'type' => 'datetime', 'list_only' => true],
        
        // Olvasott státusz
        'is_read' => [
            'label' => 'Olvasott?', 
            'type' => 'checkbox', 
            'param_type' => 'i',
            'true_value' => 1,
            'false_value' => 0
        ]
    ]
];

require '../app/generic_crud.php';
?>