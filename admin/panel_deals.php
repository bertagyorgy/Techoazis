<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_deals.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL miatt
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- ÜGYLETEK KONFIGURÁCIÓJA ---
$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'deals',
    'pk' => 'deal_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_deals',
    'page_title' => 'Megkötött Ügyletek',
    'singular_name' => 'ügylet',

    // --- LISTÁZÁS KONFIGURÁCIÓ ---
    'list_columns' => [
        'deal_id' => 'ID',
        'product_name' => 'Termék',
        'seller_username' => 'Eladó',
        'buyer_username' => 'Vevő',
        'conversation_id' => 'Beszélgetés',
        'completed_at' => 'Dátum'
    ],
    
    // LEFT JOIN használata a biztonság kedvéért (ha az eladó vagy a termék már nem létezik)
    'list_query' => "SELECT d.*, 
                            p.product_name AS product_name, 
                            s.username AS seller_username, 
                            b.username AS buyer_username
                     FROM deals d
                     LEFT JOIN products p ON d.product_id = p.product_id
                     LEFT JOIN users s ON d.seller_user_id = s.user_id
                     LEFT JOIN users b ON d.buyer_user_id = b.user_id
                     ORDER BY d.completed_at DESC",

    'list_formatters' => [
        'product_name' => function($value) {
            return htmlspecialchars($value ?? 'Törölt termék');
        },
        'seller_username' => function($value) {
            return htmlspecialchars($value ?? 'Ismeretlen');
        },
        'buyer_username' => function($value) {
            return htmlspecialchars($value ?? 'Ismeretlen');
        },
        'conversation_id' => function($value) {
            return "💬 #{$value}";
        },
        'completed_at' => function($value) {
            return date('Y.m.d. H:i', strtotime($value));
        }
    ],
    
    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => ['product_id', 'seller_user_id', 'buyer_user_id', 'conversation_id'],

    'fields' => [
        'deal_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        'product_id' => [
            'label' => 'Termék', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'products', 'value_col' => 'product_id', 'display_col' => 'product_name']
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
        
        'conversation_id' => [
            'label' => 'Beszélgetés (ID)', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'conversations', 
                'value_col' => 'conversation_id', 
                'display_col' => 'conversation_id'
            ]
        ],

        'completed_at' => ['label' => 'Lezárva', 'type' => 'datetime', 'list_only' => true],
    ]
];

require_once ROOT_PATH . '/app/generic_crud.php';
?>