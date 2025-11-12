<?php
require '../app/auth_check.php'; // Adatbázis $conn és authentikáció

// --- KOMMENTEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'COMMENTS',
    'pk' => 'comment_id',
    'page_file' => '../admin/panel_comments.php',
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
                'table' => 'POSTS',
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
                'table' => 'USERS',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        'content' => ['label' => 'Tartalom', 'type' => 'textarea', 'required' => true],
    ]
];

// --- SABLON BETÖLTÉSE ---
require '../app/generic_crud.php';
?>