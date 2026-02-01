<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_users.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- FELHASZNÁLÓK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'users',
    'pk' => 'user_id',
    // JAVÍTÁS: A page_file a központi admin routerre mutasson szép URL-el
    'page_file' => BASE_URL . '/admin/admin?page=panel_users',
    'page_title' => 'Felhasználók',
    'singular_name' => 'felhasználó',

    // Oszlopok a listázó nézetben
    'list_columns' => [
        'user_id' => 'ID',
        'username' => 'Felhasználónév',
        'email' => 'Email',
        'is_active' => 'Státusz',
        'user_role' => 'Szerepkör'
    ],

    'list_query' => "SELECT user_id, username, email, is_active, user_role FROM users ORDER BY user_id DESC LIMIT 50;",

    // Egyéni formázás a listában
    'list_formatters' => [
        'is_active' => function($value) {
            if ($value === 'A') return '✅ Aktív';
            if ($value === 'P') return '🔄 Folyamatban';
            return '❌ Inaktív/Törölt';
        },
        'user_role' => function($value) {
            return ($value === 'A') ? '⭐ Admin' : '👤 Felhasználó';
        }
    ],

    // Mezők a "Hozzáadás" és "Szerkesztés" űrlapokon
    'form_fields' => ['username', 'email', 'user_password', 'user_role', 'is_active'],

    // Adatfeldolgozás
    'preprocess_data' => function($data) {
        // --- Jelszókezelés ---
        if (!empty($data['user_password'])) {
            $data['user_password'] = password_hash($data['user_password'], PASSWORD_DEFAULT);
        } else {
            // Ha üres a jelszó mező szerkesztéskor, töröljük a tömbből, hogy ne írja felül az eredetit
            unset($data['user_password']); 
        }

        // --- Státusz kezelése ---
        // A generic_crud checkbox kezelése miatt ellenőrizzük az értéket
        if (isset($data['is_active'])) {
            $data['is_active'] = ($data['is_active'] === 'A') ? 'A' : 'IA';
        }

        return $data;
    },

    // Meződefiníciók
    'fields' => [
        'username' => ['label' => 'Felhasználónév', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'email' => ['label' => 'Email', 'type' => 'email', 'required' => true, 'param_type' => 's'],
        'user_password' => [
            'label' => 'Jelszó (üresen hagyva változatlan)',
            'type' => 'password',
            'required' => false,
            'param_type' => 's'
        ],
        'user_role' => [
            'label' => 'Szerepkör (A: Admin, F: Felhasználó)',
            'type' => 'text',
            'required' => true,
            'default' => 'F',
            'param_type' => 's'
        ],
        'is_active' => [
            'label' => 'Aktív státusz',
            'type' => 'checkbox',
            'true_value' => 'A',
            'false_value' => 'IA',
            'default' => 'A',
            'param_type' => 's'
        ]
    ]
];

// 2. A CRUD sablon behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>