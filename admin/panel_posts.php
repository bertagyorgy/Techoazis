<?php
require_once ROOT_PATH . '/app/auth_check.php';

// --- POSZTOK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'POSTS',
    'pk' => 'post_id',
    'page_file' => '../admin/panel_posts.php',
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
            return htmlspecialchars($row['group_name']); 
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
                'table' => 'USERS',
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
                'table' => 'GROUPS',
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

require '../app/generic_crud.php';
?>
