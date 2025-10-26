<?php
require 'auth_check.php';

// --- KITŰZÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'BADGES',
    'pk' => 'badge_id',
    'page_file' => 'panel_badges.php',
    'page_title' => 'Kitűzések',
    'singular_name' => 'kitűzés',

    'list_columns' => [
        'badge_id' => 'ID',
        'name' => 'Név',
        'description' => 'Leírás',
        'icon' => 'Ikon'
    ],

    'list_query' => "SELECT * FROM BADGES ORDER BY badge_id",

    'form_fields' => ['name', 'description', 'icon'],

    'fields' => [
        'name' => ['label' => 'Kitűzés neve', 'type' => 'text', 'required' => true],
        'description' => ['label' => 'Leírás', 'type' => 'textarea'],
        'icon' => ['label' => 'Ikon elérési útja', 'type' => 'text']
    ]
];

require 'generic_crud.php';
?>
