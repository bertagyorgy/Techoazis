<?php
require '../app/auth_check.php';

// --- KITŰZÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'BADGES',
    'pk' => 'badge_id',
    'page_file' => '../admin/panel_badges.php',
    'page_title' => 'Kitűzések',
    'singular_name' => 'kitűzés',

    'list_columns' => [
        'badge_id' => 'ID',
        'badge_name' => 'Név',
        'badge_description' => 'Leírás',
        'icon' => 'Ikon'
    ],

    'list_query' => "SELECT * FROM badges ORDER BY badge_id",

    'form_fields' => ['badge_name', 'badge_description', 'icon'],

    'fields' => [
        'badge_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'badge_name' => ['label' => 'Kitűzés neve', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'badge_description' => ['label' => 'Leírás', 'type' => 'textarea', 'param_type' => 's'],
        'icon' => ['label' => 'Ikon elérési útja', 'type' => 'text', 'param_type' => 's']
    ]
];

require '../app/generic_crud.php';
?>
