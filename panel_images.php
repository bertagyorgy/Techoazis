<?php
require 'auth_check.php';

// --- KÉPEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'IMAGES',
    'pk' => 'image_id',
    'page_file' => 'panel_images.php',
    'page_title' => 'Képek',
    'singular_name' => 'kép',

    'list_columns' => [
        'image_id' => 'ID',
        'product_id' => 'Termék',
        'post_id' => 'Poszt',
        'image_path' => 'Képfájl'
    ],

    'list_query' => "SELECT i.*, p.name AS product_name, po.title AS post_title
                     FROM IMAGES i
                     LEFT JOIN PRODUCTS p ON i.product_id = p.product_id
                     LEFT JOIN POSTS po ON i.post_id = po.post_id
                     ORDER BY i.image_id",

    'list_formatters' => [
        'product_id' => fn($v, $r) => $r['product_name'] ? htmlspecialchars($r['product_name']) : '-',
        'post_id' => fn($v, $r) => $r['post_title'] ? htmlspecialchars($r['post_title']) : '-',
        'image_path' => fn($v) => "<img src='" . htmlspecialchars($v) . "' style='max-width:80px;'>"
    ],

    'form_fields' => ['product_id', 'post_id', 'image_path'],

    'fields' => [
        'product_id' => [
            'label' => 'Kapcsolt termék',
            'type' => 'select',
            'foreign_key' => [
                'table' => 'PRODUCTS',
                'value_col' => 'product_id',
                'display_col' => 'name'
            ]
        ],
        'post_id' => [
            'label' => 'Kapcsolt poszt',
            'type' => 'select',
            'foreign_key' => [
                'table' => 'POSTS',
                'value_col' => 'post_id',
                'display_col' => 'title'
            ]
        ],
        'image_path' => ['label' => 'Képfájl elérési út', 'type' => 'text', 'required' => true]
    ]
];

require 'generic_crud.php';
?>
