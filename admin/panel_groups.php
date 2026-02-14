<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_groups.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// Opcionális: Ha később szeretnél automatikus fájlnevet generálni a csoportoknak is
// require_once ROOT_PATH . '/app/helpers.php';

$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'groups',
    'pk' => 'group_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_groups',
    'page_title' => 'Felhasználói Csoportok',
    'singular_name' => 'csoport',
    'allow_delete' => true,

    // --- LISTÁZÁS KONFIGURÁCIÓ ---
    'list_columns' => [
        'group_id' => 'ID',
        'group_image' => 'Kép',
        'group_name' => 'Csoport Neve',
        'group_description' => 'Leírás',
        'created_at' => 'Létrehozva'
    ],
    
    'list_query' => "SELECT * FROM groups ORDER BY group_name ASC",

    'list_formatters' => [
        'group_description' => function ($value) {
            if (empty($value)) return '<i>Nincs leírás</i>';
            return htmlspecialchars(mb_substr($value, 0, 50)) . (mb_strlen($value) > 50 ? '...' : '');
        },
        'group_image' => function ($value) {
            // A DB default értéke 'default_group.png'. Ha üres lenne, akkor is azt használjuk.
            $img_name = !empty($value) ? $value : 'default_group.png';
            $img_path = BASE_URL . "/uploads/groups/" . htmlspecialchars($img_name);
            return "<img src='{$img_path}' alt='Csoport kép' style='width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 1px solid #eee;'>";
        },
        'created_at' => function ($value) {
             return date('Y.m.d.', strtotime($value));
        }
    ],

    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => ['group_name', 'group_description', 'group_image'],

    'fields' => [
        'group_id' => [
            'label' => 'ID', 
            'type' => 'number', 
            'param_type' => 'i', 
            'list_only' => true
        ],
        'group_name' => [
            'label' => 'Csoport neve', 
            'type' => 'text', 
            'required' => true, 
            'param_type' => 's'
        ],
        'group_description' => [
            'label' => 'Csoport leírása', 
            'type' => 'textarea', 
            'param_type' => 's'
        ],
        'group_image' => [
            'label' => 'Csoport kép fájlneve', 
            'type' => 'text', 
            'default' => 'default_group.png', 
            'param_type' => 's'
        ],
        'created_at' => [
            'label' => 'Létrehozva', 
            'type' => 'datetime-local', 
            'list_only' => true
        ],
    ]
];

// 2. A CRUD sablon behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>