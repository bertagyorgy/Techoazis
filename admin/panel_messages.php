<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_messages.php

require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- ÜZENETEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'messages',
    'pk' => 'message_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_messages',
    'page_title' => 'Beszélgetés Üzenetek',
    'singular_name' => 'üzenet',

    // --- LISTÁZÁS KONFIGURÁCIÓ ---
    'list_columns' => [
        'message_id' => 'ID',
        'conversation_summary' => 'Beszélgetés (Termék)', 
        'sender_username' => 'Küldő',          
        'user_message' => 'Üzenet',
        'is_read' => 'Állapot',
        'sent_at' => 'Dátum'
    ],
    
    'list_query' => "SELECT m.*, 
                            u.username AS sender_username, 
                            p.product_name AS product_name
                     FROM messages m
                     LEFT JOIN users u ON m.sender_user_id = u.user_id
                     LEFT JOIN conversations c ON m.conversation_id = c.conversation_id
                     LEFT JOIN products p ON c.product_id = p.product_id
                     ORDER BY m.sent_at DESC",

    'list_formatters' => [
        'conversation_summary' => function ($value, $row) {
            return "#" . $row['conversation_id'] . " - " . htmlspecialchars($row['product_name'] ?? 'Törölt termék');
        },
        'user_message' => function ($value) {
            $clean = htmlspecialchars($value);
            return mb_strlen($clean) > 60 ? mb_substr($clean, 0, 60) . '...' : $clean;
        },
        'is_read' => function ($value) {
            return $value ? '<span style="color: #2ecc71;">👁️ Olvasott</span>' : '<span style="color: #e67e22;">📩 Új</span>';
        },
        'sent_at' => function ($value) {
            return date('Y.m.d. H:i', strtotime($value));
        }
    ],
    
    'form_fields' => ['conversation_id', 'sender_user_id', 'user_message', 'is_read'],

    'fields' => [
        'message_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        'conversation_id' => [
            'label' => 'Beszélgetés (ID)', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'conversations', 
                'value_col' => 'conversation_id', 
                'display_col' => 'conversation_id' // Mivel nincs neve a beszélgetésnek, marad az ID
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
            'label' => 'Üzenet szövege', 
            'type' => 'textarea', 
            'required' => true, 
            'param_type' => 's'
        ],

        'is_read' => [
            'label' => 'Olvasottnak jelölve', 
            'type' => 'checkbox', 
            'param_type' => 'i',
            'true_value' => 1,
            'false_value' => 0,
            'default' => 0
        ]
    ]
];

require_once ROOT_PATH . '/app/generic_crud.php';