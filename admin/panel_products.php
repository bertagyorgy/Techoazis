<?php
require_once ROOT_PATH . '/app/auth_check.php';

// --- TERMÉKEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'PRODUCTS',
    'pk' => 'product_id',
    'page_file' => '../admin/panel_products.php',
    'page_title' => 'Termékek',
    'singular_name' => 'termék',

    // Oszlopok a listázó nézetben
    'list_columns' => [
        'product_id' => 'ID',
        'seller_user_id' => 'Feltöltő', // JAVÍTVA: user_id -> seller_user_id
        'product_name' => 'Név',
        'category' => 'Kategória',
        'price' => 'Ár',
        'product_status' => 'Készlet',
        'main_image_url' => 'Kép', 
    ],
    
    // Egyéni JOIN-olt lekérdezés
    'list_query' => "SELECT p.*, u.username 
                     FROM products p 
                     JOIN users u ON p.seller_user_id = u.user_id 
                     ORDER BY p.product_id",
                     
    'list_formatters' => [
        'seller_user_id' => function($value, $row) { // JAVÍTVA: kulcs név
            return htmlspecialchars($row['username']); 
        },
        'price' => function($value, $row) {
            return number_format((float)$value, 0, '', ' ') . ' HUF';
        },
        'main_image_url' => function($value, $row) {
             if (empty($value)) return 'Nincs kép';
             $image_path = '../uploads/products/' . htmlspecialchars($value);
             return '<img src="' . $image_path . '" alt="Termékkép" style="max-width: 50px; height: auto; border-radius: 4px;">';
        },
        'product_status' => function($value) {
            if ($value === 'active') return '🟢 Aktív';
            if ($value === 'sold') return '🔴 Elfogyott';
            return htmlspecialchars($value);
        }
    ],
    
    // Mezők az űrlapokon - JAVÍTVA: seller_user_id
    'form_fields' => ['seller_user_id', 'product_name', 'category', 'product_description', 'price', 'product_status', 'main_image_url'],

    'fields' => [
        'product_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'seller_user_id' => [ // JAVÍTVA: user_id -> seller_user_id
            'label' => 'Feltöltő felhasználó',
            'type' => 'select', 
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'USERS',
                'value_col' => 'user_id', // Itt maradhat user_id, ha a USERS táblában ez a neve
                'display_col' => 'username'
            ]
        ],
        'product_name' => ['label' => 'Termék neve', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'category' => ['label' => 'Kategória', 'type' => 'text', 'param_type' => 's'],
        'product_description' => ['label' => 'Leírás', 'type' => 'textarea', 'param_type' => 's'], 
        'price' => ['label' => 'Ár', 'type' => 'number', 'step' => '1', 'required' => true, 'param_type' => 'i'], 
        'product_status' => [ 'label' => 'Státusz', 'type' => 'select', 'required' => true, 'param_type' => 's', 'options' => [ 'active' => 'Aktív', 'sold' => 'Eladva' ] ],        
        'main_image_url' => [
            'label' => 'Termék fő képe',
            'type' => 'file', 
            'param_type' => 's',
        ] 
    ]
];

require '../app/generic_crud.php';