<?php
// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL miatt
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- KOMMENTEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'comments',
    'pk' => 'comment_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_comments',
    'page_title' => 'Kommentek',
    'singular_name' => 'komment',

    'list_columns' => [
        'comment_id' => 'ID',
        'post_id' => 'Poszt Címe',
        'user_id' => 'Felhasználó',
        'content' => 'Tartalom (rövid)',
        'created_at' => 'Dátum'
    ],
    
    'list_query' => "SELECT c.comment_id, c.content, c.created_at, p.title AS post_title, u.username 
                     FROM comments c
                     JOIN posts p ON c.post_id = p.post_id
                     JOIN users u ON c.user_id = u.user_id
                     ORDER BY c.created_at DESC",

    'list_formatters' => [
        'post_id' => function($value, $row) { return htmlspecialchars($row['post_title']); },
        'user_id' => function($value, $row) { return htmlspecialchars($row['username']); },
        'content' => function($value) {
            $short = mb_substr($value, 0, 50);
            return htmlspecialchars($short) . (mb_strlen($value) > 50 ? '...' : '');
        }
    ],

    // Csak a 'content' módosítható, de a kulcsokat is be kell tölteni
    'form_fields' => ['post_id', 'user_id', 'content'],

    'fields' => [
        'post_id' => [
            'label' => 'Poszt',
            'type' => 'select',
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'posts',
                'value_col' => 'post_id',
                'display_col' => 'title'
            ]
        ],
        'user_id' => [
            'label' => 'Felhasználó',
            'type' => 'select',
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'users',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        'content' => ['label' => 'Tartalom', 'type' => 'textarea', 'required' => true],
    ]
];

// --- SABLON BETÖLTÉSE ---
// JAVÍTÁS: ROOT_PATH használata a CRUD behívásához
require_once ROOT_PATH . '/app/generic_crud.php';
?>