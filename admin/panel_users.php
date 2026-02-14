<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_users.php

// 1. Config betöltése kötelező a ROOT_PATH és BASE_URL eléréséhez
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- FELHASZNÁLÓK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'users',
    'pk' => 'user_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_users',
    'page_title' => 'Felhasználók',
    'singular_name' => 'felhasználó',

    // Oszlopok a listázó nézetben (kiegészítve a legfontosabb új adatokkal)
    'list_columns' => [
        'user_id' => 'ID',
        'username' => 'Felhasználónév',
        'email' => 'Email',
        'is_active' => 'Státusz',
        'user_role' => 'Szerepkör',
        'registration_date' => 'Regisztráció'
    ],

    // Lekérdezés frissítve az új oszlopokkal
    'list_query' => "SELECT user_id, username, email, is_active, user_role, registration_date FROM users ORDER BY user_id DESC LIMIT 50;",

    // Egyéni formázás a listában
    'list_formatters' => [
        'is_active' => function($value) {
            if ($value === 'A') return '✅ Aktív';
            if ($value === 'P') return '🔄 Folyamatban';
            return '❌ Inaktív/Törölt';
        },
        'user_role' => function($value) {
            return ($value === 'A') ? '⭐ Admin' : '👤 Felhasználó';
        },
        'registration_date' => function($value) {
            return date('Y.m.d. H:i', strtotime($value));
        }
    ],

    // Mezők a "Hozzáadás" és "Szerkesztés" űrlapokon (minden új oszlop hozzáadva)
    'form_fields' => [
        'username', 'username_slug', 'email', 'user_password', 'user_role', 
        'is_active', 'profile_image', 'total_posts', 'total_comments', 
        'sold_items', 'bought_items', 'avg_rating', 'ip'
    ],

    // Adatfeldolgozás
    'preprocess_data' => function($data) {
        // Jelszókezelés
        if (!empty($data['user_password'])) {
            $data['user_password'] = password_hash($data['user_password'], PASSWORD_DEFAULT);
        } else {
            unset($data['user_password']); 
        }

        // Státusz kezelése
        if (isset($data['is_active'])) {
            $data['is_active'] = ($data['is_active'] === 'A') ? 'A' : 'IA';
        }

        // Automatikus slug generálás, ha üres
        if (empty($data['username_slug']) && !empty($data['username'])) {
            $data['username_slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['username'])));
        }

        return $data;
    },

    // Meződefiníciók (Részletes beállítások az új típusokhoz)
    'fields' => [
        'username' => ['label' => 'Felhasználónév', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'username_slug' => ['label' => 'Slug (URL barát név)', 'type' => 'text', 'required' => false, 'param_type' => 's'],
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
        ],
        'profile_image' => [
            'label' => 'Profilkép elérési út',
            'type' => 'text',
            'default' => 'uploads/profile_images/anonymous.png',
            'param_type' => 's'
        ],
        'ip' => ['label' => 'Utolsó IP cím', 'type' => 'text', 'required' => false, 'param_type' => 's'],
        
        // Statisztikai adatok (Szám típusok)
        'total_posts' => ['label' => 'Posztok száma', 'type' => 'number', 'default' => 0, 'param_type' => 'i'],
        'total_comments' => ['label' => 'Kommentek száma', 'type' => 'number', 'default' => 0, 'param_type' => 'i'],
        'sold_items' => ['label' => 'Eladott termékek', 'type' => 'number', 'default' => 0, 'param_type' => 'i'],
        'bought_items' => ['label' => 'Vásárolt termékek', 'type' => 'number', 'default' => 0, 'param_type' => 'i'],
        'avg_rating' => [
            'label' => 'Átlag értékelés (pl. 4.50)', 
            'type' => 'number', 
            'step' => '0.01', 
            'default' => 0.00, 
            'param_type' => 'd'
        ]
    ]
];

// 2. A CRUD sablon behívása ROOT_PATH használatával
require_once ROOT_PATH . '/app/generic_crud.php';
?>