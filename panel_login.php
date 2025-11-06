<?php
require 'auth_check.php'; // Adatbázis $conn és authentikáció

// --- BEJELENTKEZÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'LOGIN',
    'pk' => 'login_id',
    'page_file' => 'panel_login.php',
    'page_title' => 'Bejelentkezési Napló',
    'singular_name' => 'bejegyzés',
    
    // Műveletek letiltása
    'allow_edit' => false,
    'allow_delete' => false,
    'allow_add' => true, // Manuális hozzáadás engedélyezése (a kérésed alapján)

    'list_columns' => [
        'login_id' => 'ID',
        'user_id' => 'Felhasználó',
        'login_date' => 'Dátum'
    ],
    
    'list_query' => "SELECT l.login_id, l.login_date, u.username 
                     FROM LOGIN l
                     JOIN USERS u ON l.user_id = u.user_id
                     ORDER BY l.login_date DESC LIMIT 15",

    'list_formatters' => [
        'user_id' => function($value, $row) { return htmlspecialchars($row['username']); },
    ],

    // Csak a user_id-t lehet megadni hozzáadáskor
    'form_fields' => ['user_id'],

    'fields' => [
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
    ]
];

// --- SABLON BETÖLTÉSE ---
require 'generic_crud.php';
?>