<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_product_images.php

require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

$config = [
    'table' => 'product_images',
    'pk' => 'image_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_product_images',
    'page_title' => 'Termék képek',
    'singular_name' => 'termék kép',

    'list_columns' => [
        'image_id' => 'ID',
        'product_id' => 'Termék',
        'is_primary' => 'Főkép',
        'image_preview' => 'Kép'
    ],

    'list_query' => "SELECT 
                        i.image_id,
                        i.product_id,
                        i.image_path,
                        i.is_primary,
                        p.product_name
                    FROM product_images i
                    LEFT JOIN products p ON i.product_id = p.product_id
                    ORDER BY i.image_id DESC",

    'list_formatters' => [
        'product_id' => function($value, $row) { 
            return htmlspecialchars($row['product_name'] ?? 'Ismeretlen termék'); 
        },
        'is_primary' => function($value) {
            return $value ? '<b style="color: #2ecc71;">Igen</b>' : 'Nem';
        },
        'image_preview' => function ($value, $row) { 
            $img_url = BASE_URL . '/' . htmlspecialchars($row['image_path']);
            return "<img src='{$img_url}' alt='Kép' style='width: 50px; height: 50px; object-fit: cover; border-radius: 4px;'>";
        }
    ],

    'fields' => [
        'image_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'product_id' => [
            'label' => 'Kapcsolt termék',
            'type' => 'select',
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'products',
                'value_col' => 'product_id',
                'display_col' => 'product_name'
            ]
        ],
        'image_path' => ['label' => 'Képfájl elérési út', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'is_primary' => ['label' => 'Borítókép (0 vagy 1)', 'type' => 'number', 'param_type' => 'i'],
        'sort_order' => ['label' => 'Rendezés', 'type' => 'number', 'param_type' => 'i']
    ]
];

require_once ROOT_PATH . '/app/generic_crud.php';