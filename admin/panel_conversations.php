<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_conversations.php

require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- BESZÉLGETÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'conversations',
    'pk' => 'conversation_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_conversations',
    'page_title' => 'Beszélgetések / Alkuk',
    'singular_name' => 'beszélgetés',

    // --- LISTÁZÁS KONFIGURÁCIÓ ---
    'list_columns' => [
        'conversation_id' => 'ID',
        'product_name' => 'Termék',
        'seller_username' => 'Eladó', 
        'buyer_username' => 'Vevő', 
        'conv_status' => 'Státusz',
        'agreements' => 'Megegyezés',
        'created_at' => 'Létrehozva'
    ],
    
    'list_query' => "SELECT c.*, 
                            p.product_name AS product_name, 
                            s.username AS seller_username, 
                            b.username AS buyer_username
                     FROM conversations c
                     LEFT JOIN products p ON c.product_id = p.product_id
                     LEFT JOIN users s ON c.seller_user_id = s.user_id
                     LEFT JOIN users b ON c.buyer_user_id = b.user_id
                     ORDER BY c.created_at DESC",

    'list_formatters' => [
        'conv_status' => function ($value) {
            $statuses = [
                'open' => '💬 Nyitott',
                'deal_made' => '✅ Üzlet megköttetett',
                'archived' => '📁 Archivált'
            ];
            return $statuses[$value] ?? htmlspecialchars($value);
        },
        'agreements' => function ($value, $row) {
            $s = $row['is_seller_agreed'] ? '🤝 Eladó OK' : '⏳ Eladó vár';
            $b = $row['is_buyer_agreed'] ? '🤝 Vevő OK' : '⏳ Vevő vár';
            return "<small>$s<br>$b</small>";
        },
        'created_at' => function ($value) {
            return date('Y.m.d. H:i', strtotime($value));
        }
    ],
    
    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => [
        'product_id', 
        'seller_user_id', 
        'buyer_user_id', 
        'conv_status', 
        'is_seller_agreed', 
        'is_buyer_agreed'
    ],

    'fields' => [
        'product_id' => [
            'label' => 'Termék', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'products', 
                'value_col' => 'product_id', 
                'display_col' => 'product_name'
            ]
        ],
        'seller_user_id' => [
            'label' => 'Eladó', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'users', 'value_col' => 'user_id', 'display_col' => 'username']
        ],
        'buyer_user_id' => [
            'label' => 'Vevő', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'users', 'value_col' => 'user_id', 'display_col' => 'username']
        ],
        'conv_status' => [
            'label' => 'Státusz', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 's',
            'options' => [
                'open' => '💬 Nyitott',
                'deal_made' => '✅ Megkötve',
                'archived' => '📁 Archivált'
            ],
            'default' => 'open'
        ],
        'is_seller_agreed' => [
            'label' => 'Eladó elfogadta az alkut', 
            'type' => 'checkbox', 
            'true_value' => 1, 
            'false_value' => 0, 
            'param_type' => 'i'
        ],
        'is_buyer_agreed' => [
            'label' => 'Vevő elfogadta az alkut', 
            'type' => 'checkbox', 
            'true_value' => 1, 
            'false_value' => 0, 
            'param_type' => 'i'
        ]
    ]
];

require_once ROOT_PATH . '/app/generic_crud.php';