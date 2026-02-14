<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_post_images.php

require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

$config = [
    'table' => 'post_images',
    'pk' => 'image_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_post_images',
    'page_title' => 'Poszt képek',
    'singular_name' => 'poszt kép',

    'list_columns' => [
        'image_id' => 'ID',
        'post_id' => 'Poszt címe',
        'image_preview' => 'Kép'
    ],

    'list_query' => "SELECT 
                        i.image_id,
                        i.post_id,
                        i.image_path,
                        po.title AS post_title
                    FROM post_images i
                    LEFT JOIN posts po ON i.post_id = po.post_id
                    ORDER BY i.image_id DESC",

    'list_formatters' => [
        'post_id' => function($value, $row) { 
            return htmlspecialchars($row['post_title'] ?? 'Nincs poszt'); 
        },
        'image_preview' => function ($value, $row) { 
            $img_url = BASE_URL . '/' . htmlspecialchars($row['image_path']);
            return "<img src='{$img_url}' alt='Kép' style='width: 50px; height: 50px; object-fit: cover; border-radius: 4px;'>";
        }
    ],

    // JAVÍTÁS: Ez a rész hiányzott, ezért dobott hibát a generic_crud!
    'form_fields' => ['post_id', 'image_path', 'sort_order'],

    'fields' => [
        'image_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'post_id' => [
            'label' => 'Kapcsolt poszt',
            'type' => 'select',
            'required' => true, // Érdemes kötelezővé tenni
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'posts',
                'value_col' => 'post_id',
                'display_col' => 'title'
            ]
        ],
        'image_path' => [
            'label' => 'Képfájl elérési út', 
            'type' => 'text', 
            'required' => true, 
            'param_type' => 's'
        ],
        'sort_order' => [
            'label' => 'Rendezési sorrend', 
            'type' => 'number', 
            'default' => 1,
            'param_type' => 'i'
        ]
    ]
];

require_once ROOT_PATH . '/app/generic_crud.php';