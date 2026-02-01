<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_conversations.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL miatt
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- BESZÉLGETÉSEK KONFIGURÁCIÓJA ---
$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'conversations',
    'pk' => 'conversation_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_conversations',
    'page_title' => 'Beszélgetések',
    'singular_name' => 'beszélgetés',

    // --- LISTÁZÁS KONFIGURÁCIÓ ---
    'list_columns' => [
        'conversation_id' => 'ID',
        'product_name' => 'Termék',
        'seller_username' => 'Eladó', 
        'buyer_username' => 'Vevő', 
        'conv_status' => 'Státusz',
        'created_at' => 'Létrehozva',
        'updated_at' => 'Frissítve'
    ],
    
    // JOIN-ok a termék nevéhez, az eladó és a vevő felhasználónevéhez
    'list_query' => "SELECT c.*, 
                            p.product_name AS product_name, 
                            s.username AS seller_username, 
                            b.username AS buyer_username
                     FROM conversations c
                     JOIN products p ON c.product_id = p.product_id
                     JOIN users s ON c.seller_user_id = s.user_id
                     JOIN users b ON c.buyer_user_id = b.user_id
                     ORDER BY c.created_at DESC",

    // Formázó a státuszhoz
    'list_formatters' => [
        'conv_status' => function ($value, $row) {
            $statuses = [
                'open' => '💬 Nyitott',
                'deal_made' => '💰 Megkötve',
                'cancelled' => '🚫 Törölve'
            ];
            return $statuses[$value] ?? $value;
        }
    ],
    
    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => ['product_id', 'seller_user_id', 'buyer_user_id', 'conv_status'],

    'fields' => [
        'conversation_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        // Termék kiválasztása
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
        
        // Eladó felhasználó kiválasztása
        'seller_user_id' => [
            'label' => 'Eladó', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'users', 'value_col' => 'user_id', 'display_col' => 'username']
        ],
        
        // Vevő felhasználó kiválasztása
        'buyer_user_id' => [
            'label' => 'Vevő', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'users', 'value_col' => 'user_id', 'display_col' => 'username']
        ],
        
        // Státusz (ENUM) kiválasztása
        'conv_status' => [
            'label' => 'Státusz', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 's',
            'options' => [
                'open' => '💬 Nyitott',
                'deal_made' => '💰 Megkötve',
                'cancelled' => '🚫 Törölve'
            ],
            'default' => 'open'
        ],

        // Létrehozás és frissítés időpontja (csak listázáshoz)
        'created_at' => ['label' => 'Létrehozva', 'type' => 'datetime', 'list_only' => true],
        'updated_at' => ['label' => 'Frissítve', 'type' => 'datetime', 'list_only' => true],
    ]
];

// JAVÍTÁS: Sablon betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>