<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_images.php

// 1. Config betöltése a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- KÉPEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'images',
    'pk' => 'image_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_images',
    'page_title' => 'Képek',
    'singular_name' => 'kép',

    'list_columns' => [
        'image_id' => 'ID',
        'post_id' => 'Poszt',
        'image_path' => 'Képútvonal',
        'image_preview' => 'Kép'
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
        'post_id' => function($value, $row) { 
            return htmlspecialchars($row['post_title'] ?? 'Nincs poszt'); 
        },
        'image_path' => function($value, $row) { 
            return htmlspecialchars($value); 
        },
        'image_preview' => function ($value, $row) { 
            // JAVÍTÁS: BASE_URL használata a kép betöltéséhez, hogy bárhonnan megjelenjen
            $img_url = BASE_URL . '/' . htmlspecialchars($row['image_path']);
            return "<img src='{$img_url}' alt='Kép' style='width: 50px; height: 50px; object-fit: cover; border-radius: 4px;'>";
        }
    ],

    'form_fields' => ['post_id', 'product_id', 'image_path'],

    'fields' => [
        'image_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'product_id' => [
            'label' => 'Kapcsolt termék',
            'type' => 'select',
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'products',
                'value_col' => 'product_id',
                'display_col' => 'product_name' // JAVÍTÁS: name helyett product_name
            ]
        ],
        'post_id' => [
            'label' => 'Kapcsolt poszt',
            'type' => 'select',
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'posts',
                'value_col' => 'post_id',
                'display_col' => 'title'
            ]
        ],
        'image_path' => ['label' => 'Képfájl elérési út (pl. static/images/valami.jpg)', 'type' => 'text', 'required' => true, 'param_type' => 's']
    ]
];

// 2. A CRUD sablon behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>