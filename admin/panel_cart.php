<?php
require '../app/auth_check.php';

// --- KOSÁR KONFIGURÁCIÓJA ---
$config = [
    'table' => 'CART',
    'pk' => 'cart_id',
    'page_file' => '../admin/panel_cart.php',
    'page_title' => 'Kosár',
    'singular_name' => 'tétel',

    'list_columns' => [
        'cart_id' => 'ID',
        'user_id' => 'Felhasználó',
        'product_id' => 'Termék',
        'quantity' => 'Mennyiség',
        'added_at' => 'Hozzáadva'
    ],

    'list_query' => "SELECT c.*, u.username, p.product_name AS product_name
                     FROM cart c
                     JOIN users u ON c.user_id = u.user_id
                     JOIN products p ON c.product_id = p.product_id
                     ORDER BY c.added_at DESC",

    'list_formatters' => [
        'user_id' => function($value, $row) { return htmlspecialchars($row['username']);},
        'product_id' => function($value, $row) { return htmlspecialchars($r['product_name']);}
    ],

    'form_fields' => ['user_id', 'product_id', 'quantity'],

    'fields' => [
        'user_id' => [
            'label' => 'Felhasználó',
            'type' => 'select',
            'required' => true,
            'foreign_key' => [
                'table' => 'USERS',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        'product_id' => [
            'label' => 'Termék',
            'type' => 'select',
            'required' => true,
            'foreign_key' => [
                'table' => 'PRODUCTS',
                'value_col' => 'product_id',
                'display_col' => 'name'
            ]
        ],
        'quantity' => ['label' => 'Mennyiség', 'type' => 'number', 'default' => 1, 'param_type' => 'i']
    ]
];

require '../app/generic_crud.php';
?>
