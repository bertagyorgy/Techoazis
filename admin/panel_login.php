<?php
require '../app/auth_check.php'; // Adatbázis $conn és authentikáció

// --- BEJELENTKEZÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'LOGIN',
    'pk' => 'login_id',
    'page_file' => '../admin/panel_login.php',
    'page_title' => 'Bejelentkezési Napló',
    'singular_name' => 'bejegyzés',
    
    // Műveletek letiltása
    'allow_edit' => false,
    'allow_delete' => false,
    'allow_add' => true, // Manuális hozzáadás engedélyezése (a kérésed alapján)

    'list_columns' => [
        'login_id' => 'ID',
        'user_id' => 'Felhasználó',
        'login_date' => 'Dátum',
        'user_ip' => 'IP cím'
    ],
    
    'list_query' => "SELECT l.login_id, l.login_date, u.username, u.ip
                     FROM LOGIN l
                     JOIN users u ON l.user_id = u.user_id
                     ORDER BY l.login_date DESC LIMIT 10",

    'list_formatters' => [
        'user_id' => function($value, $row) { return htmlspecialchars($row['username']); },
        'user_ip' => function($value, $row) { return htmlspecialchars($row['ip']); }
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
require '../app/generic_crud.php';
?>