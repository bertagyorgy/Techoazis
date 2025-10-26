<?php
require 'auth_check.php'; // Adatbázis $conn és authentikáció

// --- FELHASZNÁLÓK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'users',
    'pk' => 'user_id',
    'page_file' => 'panel_users.php',
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
    
    // Egyéni formázás a listában
    'list_formatters' => [
        'is_active' => function($value) {
            return $value === 'A' ? '✅ Aktív' : '❌ Inaktív';
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
            unset($data['user_password']); // Ha üres, ne frissítse
        }

        // --- Státusz kezelése ---
        if (isset($data['is_active'])) {
            $data['is_active'] = ($data['is_active'] === 'A') ? 'A' : 'IA';
        }

        return $data;
    },

    // Meződefiníciók
    'fields' => [
        'username' => ['label' => 'Felhasználónév', 'type' => 'text', 'required' => true],
        'email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
        'user_password' => [
            'label' => 'Jelszó',
            'type' => 'password',
            'required' => false
        ],
        'user_role' => [
            'label' => 'Szerepkör',
            'type' => 'text',
            'required' => true,
            'default' => 'F'
        ],
        'is_active' => [
            'label' => 'Aktív',
            'type' => 'checkbox',
            'true_value' => 'A',
            'false_value' => 'IA',
            'default' => 'A'
        ]
    ]
];

// --- SABLON BETÖLTÉSE ---
require 'generic_crud.php';
?>
