<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_login.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- BEJELENTKEZÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'login', // Kisbetűs táblanév az egységességért
    'pk' => 'login_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson
    'page_file' => BASE_URL . '/admin/admin?page=panel_login',
    'page_title' => 'Bejelentkezési Napló',
    'singular_name' => 'bejelentkezés',
    
    // Műveletek korlátozása (Naplózásnál az edit/delete általában tiltott)
    'allow_edit' => false,
    'allow_delete' => false,
    'allow_add' => true, 

    'list_columns' => [
        'login_id' => 'ID',
        'user_id' => 'Felhasználó',
        'login_date' => 'Dátum',
        'user_ip' => 'IP cím'
    ],
    
    // JAVÍTÁS: Táblanevek kisbetűvel a kompatibilitás miatt
    'list_query' => "SELECT l.login_id, l.login_date, u.username, u.ip
                     FROM login l
                     JOIN users u ON l.user_id = u.user_id
                     ORDER BY l.login_date DESC LIMIT 50", // Megemeltem a limitet, hogy hasznosabb legyen

    'list_formatters' => [
        'user_id' => function($value, $row) { return htmlspecialchars($row['username']); },
        'user_ip' => function($value, $row) { return htmlspecialchars($row['ip'] ?? 'Nincs adat'); }
    ],

    'form_fields' => ['user_id'],

    'fields' => [
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
    ]
];

// 2. A CRUD sablon behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>