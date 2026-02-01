<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_groups.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'groups',
    'pk' => 'group_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_groups',
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
            return htmlspecialchars(mb_substr($value, 0, 50)) . (mb_strlen($value) > 50 ? '...' : '');
        },
        'group_image' => function ($value, $row) {
            // JAVÍTÁS: BASE_URL használata a kép betöltéséhez, hogy bárhonnan megjelenjen
            $img_path = BASE_URL . "/uploads/groups/" . htmlspecialchars($value);
            return "<img src='{$img_path}' alt='Csoport kép' style='width: 50px; height: 50px; object-fit: cover; border-radius: 4px;'>";
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
        // Ha a generic_crud támogatja a fájlfeltöltést, átállítható 'type' => 'file'-ra is
        'group_image' => ['label' => 'Kép fájlnév', 'type' => 'text', 'default' => 'default_group.png', 'param_type' => 's'],
        'created_at' => ['label' => 'Létrehozva', 'type' => 'datetime-local', 'list_only' => true],
    ]
];

// 2. A CRUD sablon behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>