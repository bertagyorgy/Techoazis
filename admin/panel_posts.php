<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_posts.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- POSZTOK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'posts', // Kisbetűs táblanév a kompatibilitásért
    'pk' => 'post_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_posts',
    'page_title' => 'Posztok',
    'singular_name' => 'poszt',

    'list_columns' => [
        'post_id' => 'ID',
        'user_id' => 'Szerző',
        'group_id' => 'Csoport',
        'title' => 'Cím',
        'created_at' => 'Dátum'
    ],

    'list_query' => "SELECT p.*, u.username, g.group_name
                     FROM posts p
                     JOIN users u ON p.user_id = u.user_id
                     JOIN groups g ON p.group_id = g.group_id
                     ORDER BY p.created_at DESC",

    'list_formatters' => [
        'user_id' => function($value, $row) { 
            return htmlspecialchars($row['username']); 
        },
        'group_id' => function($value, $row) { 
            return htmlspecialchars($row['group_name'] ?? 'Nincs csoport'); 
        }
    ],

    'form_fields' => ['user_id', 'group_id', 'title', 'content'],

    'fields' => [
        'user_id' => [
            'label' => 'Szerző',
            'type' => 'select',
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'users',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],

        'group_id' => [
            'label' => 'Csoport',
            'type' => 'select',
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'groups',
                'value_col' => 'group_id',
                'display_col' => 'group_name'
            ]
        ],

        'title' => [
            'label' => 'Cím',
            'type' => 'text',
            'required' => true
        ],

        'content' => [
            'label' => 'Tartalom',
            'type' => 'textarea',
            'required' => true
        ]
    ]
];

// 2. A CRUD sablon behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>