<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_deals.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL miatt
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- ÜGYLETEK KONFIGURÁCIÓJA ---
$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'deals',
    'pk' => 'deal_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_deals',
    'page_title' => 'Megkötött Ügyletek',
    'singular_name' => 'ügylet',

    // --- LISTÁZÁS KONFIGURÁCIÓ (JOIN-okkal) ---
    'list_columns' => [
        'deal_id' => 'ID',
        'product_name' => 'Termék',        // product_id-ból JOIN-nal
        'seller_username' => 'Eladó',      // seller_user_id-ből JOIN-nal
        'buyer_username' => 'Vevő',        // buyer_user_id-ből JOIN-nal
        'conversation_id' => 'Beszélgetés ID',
        'completed_at' => 'Lezárva'
    ],
    
    // JOIN-ok: product_name, seller_username, buyer_username
    'list_query' => "SELECT d.*, 
                            p.product_name AS product_name, 
                            s.username AS seller_username, 
                            b.username AS buyer_username
                     FROM deals d
                     JOIN products p ON d.product_id = p.product_id
                     JOIN users s ON d.seller_user_id = s.user_id
                     JOIN users b ON d.buyer_user_id = b.user_id
                     ORDER BY d.completed_at DESC",

    // Nincs szükség külön formázóra
    'list_formatters' => [],
    
    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => ['product_id', 'seller_user_id', 'buyer_user_id', 'conversation_id'],

    'fields' => [
        'deal_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        // Termék kiválasztása
        'product_id' => [
            'label' => 'Termék', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'products', 'value_col' => 'product_id', 'display_col' => 'product_name']
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
        
        // Beszélgetés kiválasztása
        'conversation_id' => [
            'label' => 'Beszélgetés', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'conversations', 
                'value_col' => 'conversation_id', 
                'display_col' => 'conversation_id'
            ]
        ],

        // Lezárás időpontja (csak listázáshoz)
        'completed_at' => ['label' => 'Lezárva', 'type' => 'datetime', 'list_only' => true],
    ]
];

// JAVÍTÁS: Sablon betöltése ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>