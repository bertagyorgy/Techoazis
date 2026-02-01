<?php
// panel_groups.php
// Feltételezzük, hogy a $conn kapcsolat és az auth_check.php elérhető.
require_once ROOT_PATH . '/app/auth_check.php';

$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'groups',
    'pk' => 'group_id',
    'page_file' => '../admin/panel_groups.php',
    'page_title' => 'Felhasználói Csoportok',
    'singular_name' => 'csoport',
    'allow_delete' => true,

    // --- LISTÁZÁS KONFIGURÁCIÓ ---
    'list_columns' => [
        'group_id' => 'ID',
        'group_name' => 'Csoport Neve',
        'group_description' => 'Leírás (rövidítve)',
        'created_at' => 'Létrehozva',
        'group_image' => 'Kép'
    ],
    
    // Formázók a listához
    'list_formatters' => [
        'group_description' => function ($value, $row) {
            return htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '');
        },
        'group_image' => function ($value, $row) {
            return "<img src='../uploads/groups/{$value}' alt='Csoport kép' style='width: 50px; height: 50px; object-fit: cover;'>";
        },
        'created_at' => function ($value, $row) {
             return date('Y.m.d H:i', strtotime($value));
        }
    ],

    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => ['group_name', 'group_description', 'group_image'],

    // Részletes meződefiníciók
    'fields' => [
        'group_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'group_name' => ['label' => 'Csoport neve', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'group_description' => ['label' => 'Leírás', 'type' => 'textarea', 'param_type' => 's'],
        'group_image' => ['label' => 'Kép fájlnév', 'type' => 'text', 'default' => 'default_group.png', 'param_type' => 's'],
        'created_at' => ['label' => 'Létrehozva', 'type' => 'datetime-local', 'list_only' => true],
    ]
];

require '../app/generic_crud.php';
?>