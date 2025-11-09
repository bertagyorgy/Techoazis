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
        'category' => 'Kategória',
        'created_at' => 'Dátum'
    ],

    'list_query' => "SELECT p.*, u.username 
                     FROM POSTS p 
                     JOIN USERS u ON p.user_id = u.user_id
                     ORDER BY p.created_at DESC",

    'list_formatters' => [
        'user_id' => fn($v, $r) => htmlspecialchars($r['username']),
    ],

    'form_fields' => ['user_id', 'title', 'content', 'category', 'code_snippet', 'language'],

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
        'content' => ['label' => 'Tartalom', 'type' => 'textarea', 'required' => true],
        'category' => ['label' => 'Kategória', 'type' => 'text'],
        'code_snippet' => ['label' => 'Kódrészlet', 'type' => 'textarea'],
        'language' => ['label' => 'Programnyelv', 'type' => 'text']
    ]
];

require '../app/generic_crud.php';
?>
