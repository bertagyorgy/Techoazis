<?php
require '../app/auth_check.php';

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
        'title' => 'Cím',
        'created_at' => 'Dátum'
    ],

    'list_query' => "SELECT p.*, u.username 
                     FROM posts p 
                     JOIN users u ON p.user_id = u.user_id
                     ORDER BY p.created_at DESC",

    'list_formatters' => [
        'user_id' => function($value, $row) { return htmlspecialchars($row['username']); },
    ],

    'form_fields' => ['user_id', 'title', 'content'],

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
        'title' => ['label' => 'Cím', 'type' => 'text', 'required' => true],
        'content' => ['label' => 'Tartalom', 'type' => 'textarea', 'required' => true]
    ]
];

require '../app/generic_crud.php';
?>
