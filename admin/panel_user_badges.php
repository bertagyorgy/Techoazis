<?php
require '../app/auth_check.php';

// --- FELHASZNÁLÓ KITŰZÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'USER_BADGES',
    'pk' => null,
    'composite_pk' => ['user_id', 'badge_id'],
    'page_file' => '../admin/panel_user_badges.php',
    'page_title' => 'Felhasználói Kitűzések',
    'singular_name' => 'felhasználói kitűzés',

    'list_columns' => [
        'user_id' => 'Felhasználó',
        'badge_id' => 'Kitűzés',
        'earned_at' => 'Megszerezve'
    ],

    'list_query' => "SELECT ub.*, u.username, b.name AS badge_name 
                     FROM USER_BADGES ub
                     JOIN USERS u ON ub.user_id = u.user_id
                     JOIN BADGES b ON ub.badge_id = b.badge_id
                     ORDER BY ub.earned_at DESC",

    'list_formatters' => [
        'user_id' => fn($v, $r) => htmlspecialchars($r['username']),
        'badge_id' => fn($v, $r) => htmlspecialchars($r['badge_name'])
    ],

    'form_fields' => ['user_id', 'badge_id'],

    'fields' => [
        'user_id' => [
            'label' => 'Felhasználó',
            'type' => 'select',
            'required' => true,
            'foreign_key' => [
                'table' => 'USERS',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        'badge_id' => [
            'label' => 'Kitűzés',
            'type' => 'select',
            'required' => true,
            'foreign_key' => [
                'table' => 'BADGES',
                'value_col' => 'badge_id',
                'display_col' => 'name'
            ]
        ]
    ]
];

require '../app/generic_crud.php';
?>
