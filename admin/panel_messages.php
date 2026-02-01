<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_messages.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- ÜZENETEK KONFIGURÁCIÓJA ---
$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'messages',
    'pk' => 'message_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_messages',
    'page_title' => 'Beszélgetés Üzenetek',
    'singular_name' => 'üzenet',

    // --- LISTÁZÁS KONFIGURÁCIÓ (JOIN-nal a küldőhöz és a beszélgetéshez) ---
    'list_columns' => [
        'message_id' => 'ID',
        'conversation_summary' => 'Beszélgetés', 
        'sender_username' => 'Küldő',          
        'user_message' => 'Üzenet',
        'sent_at' => 'Elküldve',
        'is_read' => 'Olvasott?'
    ],
    
    'list_query' => "SELECT m.*, 
                            u.username AS sender_username, 
                            CONCAT(p.product_name, ' (Vevő: ', b.username, ')') AS conversation_summary 
                     FROM messages m
                     JOIN users u ON m.sender_user_id = u.user_id
                     JOIN conversations c ON m.conversation_id = c.conversation_id
                     JOIN products p ON c.product_id = p.product_id
                     JOIN users b ON c.buyer_user_id = b.user_id 
                     ORDER BY m.sent_at DESC",

    // Formázók
    'list_formatters' => [
        'user_message' => function ($value, $row) {
            // JAVÍTÁS: mb_substr használata az ékezetes karakterek védelmében
            return mb_strlen($value) > 50 ? htmlspecialchars(mb_substr($value, 0, 50)) . '...' : htmlspecialchars($value);
        },
        'is_read' => function ($value, $row) {
            return $value ? '✅ Igen' : '❌ Nem';
        }
    ],
    
    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => ['conversation_id', 'sender_user_id', 'user_message', 'is_read'],

    'fields' => [
        'message_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        'conversation_id' => [
            'label' => 'Beszélgetés ID', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'conversations', 
                'value_col' => 'conversation_id', 
                'display_col' => 'conversation_id' 
            ]
        ],
        
        'sender_user_id' => [
            'label' => 'Küldő felhasználó', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'users', 'value_col' => 'user_id', 'display_col' => 'username']
        ],
        
        'user_message' => [
            'label' => 'Üzenet', 
            'type' => 'textarea', 
            'required' => true, 
            'param_type' => 's'
        ],

        'sent_at' => ['label' => 'Elküldve', 'type' => 'datetime', 'list_only' => true],
        
        'is_read' => [
            'label' => 'Olvasott?', 
            'type' => 'checkbox', 
            'param_type' => 'i',
            'true_value' => 1,
            'false_value' => 0
        ]
    ]
];

// JAVÍTÁS: Sablon betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>