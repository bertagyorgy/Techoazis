<?php
require_once ROOT_PATH . '/app/auth_check.php';

// --- KÉPEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'IMAGES',
    'pk' => 'image_id',
    'page_file' => '../admin/panel_images.php',
    'page_title' => 'Képek',
    'singular_name' => 'kép',

    'list_columns' => [
        'image_id' => 'ID',
        'post_id' => 'Poszt',
        'image_path' => 'Képútvonal',
        'image' => 'Kép'
    ],

    'list_query' => "SELECT 
                        i.image_id,
                        i.post_id,
                        i.image_path,
                        po.title AS post_title
                    FROM images i
                    LEFT JOIN posts po ON i.post_id = po.post_id
                    ORDER BY i.image_id;
                    ",

    'list_formatters' => [
        'image_id' => function($value, $row) { return htmlspecialchars($row['image_id']); },
        'post_id' => function($value, $row) { return htmlspecialchars($row['post_title']); },
        'image_path' => function($value, $row) { return htmlspecialchars($row['image_path']);},
        'image' => function ($value, $row) { return "<img src='../{$row['image_path']}' alt='Poszt kép' style='width: 50px; height: 50px; object-fit: cover'>";}
    ],

    'form_fields' => ['image_id', 'post_id', 'image_path', 'image'],

    'fields' => [
        'image_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true], // ÚJ SOR
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
        'image_path' => ['label' => 'Képfájl elérési út', 'type' => 'text', 'required' => true],
        'image' => ['label' => 'Kép fájlnév', 'type' => 'text', 'param_type' => 's']
    ]
];

require '../app/generic_crud.php';
?>
