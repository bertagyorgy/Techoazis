<?php
require '../app/auth_check.php';

// --- RENDELÉSI TÉTELEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'order_details',
    'pk' => 'detail_id',
    'page_file' => '../admin/panel_order_details.php',
    'page_title' => 'Rendelési tételek',
    'singular_name' => 'rendelési tétel',

    'list_columns' => [
        'detail_id' => 'ID',
        'order_id' => 'Rendelés ID', 
        'product_id' => 'Termék neve', 
        'quantity' => 'Mennyiség',
        'price_snapshot' => 'Egységár'
    ],
    
    // JOIN-olt lekérdezés a terméknév és a rendelés ID alapján
    // JAVÍTVA: p.name helyett p.product_name a sémának megfelelően
    'list_query' => "
        SELECT 
            od.*, 
            p.product_name AS product_name
        FROM order_details od 
        JOIN products p ON od.product_id = p.product_id
        ORDER BY od.detail_id DESC
    ",

    'list_formatters' => [
        'product_id' => function($value, $row) {
            // A termék ID helyett a termék nevét mutatjuk
            // Használja az aliast: 'product_name'
            return htmlspecialchars($row['product_name'] ?? 'Termék törölve');
        },
        'price_snapshot' => function($value, $row) {
            return number_format((float)$value, 2, ',', '.') . ' HUF';
        }
    ],

    'form_fields' => ['order_id', 'product_id', 'quantity', 'price_snapshot'],

    'fields' => [
        'detail_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        'order_id' => [
            'label' => 'Rendelés ID',
            'type' => 'select', 
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'orders',
                'value_col' => 'order_id',
                'display_col' => 'order_id' // Vagy valami más a ORDERS táblából
            ]
        ],
        
        'product_id' => [
            'label' => 'Termék',
            'type' => 'select', 
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'products',
                'value_col' => 'product_id',
                // JAVÍTVA: name helyett product_name a sémának megfelelően
                'display_col' => 'product_name' 
            ]
        ],
        
        'quantity' => [
            'label' => 'Mennyiség', 
            'type' => 'number', 
            'required' => true, 
            'param_type' => 'i' // INT
        ],
        
        'price_snapshot' => [
            'label' => 'Egységár (felvételkor)', 
            'type' => 'number', 
            'step' => '0.01', 
            'required' => true, 
            'param_type' => 'd' // DECIMAL
        ]
    ]
];

require '../app/generic_crud.php';