<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_products.php

// 1. Config betöltése kötelező
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

// --- TERMÉKEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'products',
    'pk' => 'product_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_products',
    'page_title' => 'Termékek',
    'singular_name' => 'termék',

    // Oszlopok a listázó nézetben
    'list_columns' => [
        'product_id' => 'ID',
        'main_image_url' => 'Kép', 
        'product_name' => 'Név',
        'username' => 'Eladó', // A JOIN-ból jön
        'category' => 'Kategória',
        'price' => 'Ár',
        'product_status' => 'Státusz',
        'created_at' => 'Létrehozva'
    ],
    
    // Lekérdezés az új mezőkkel és JOIN-nal
    'list_query' => "SELECT p.*, u.username,
                     (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) as main_image
                     FROM products p 
                     LEFT JOIN users u ON p.seller_user_id = u.user_id 
                     ORDER BY p.product_id DESC LIMIT 50;",
                     
    'list_formatters' => [
        'price' => function($value) {
            return number_format((float)$value, 0, '', ' ') . ' Ft';
        },
        'main_image_url' => function($value, $row) {
            // A shop.php logikáját követve: ha nincs a product_images-ben kép, akkor az alapértelmezettet mutatjuk
            $image_to_show = !empty($row['main_image']) ? $row['main_image'] : 'uploads/products/default_product.png';
            
            // Webes elérési út összeállítása
            $img_url = BASE_URL . '/' . $image_to_show;
            
            return '<img src="' . $img_url . '" alt="Termék" style="max-width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">';
        },
        'product_status' => function($value) {
            $badges = [
                'active' => '<span style="color: green;">🟢 Aktív</span>',
                'sold'   => '<span style="color: red;">🔴 Eladva</span>',
                'hidden' => '<span style="color: gray;">⚪ Rejtett</span>'
            ];
            return $badges[$value] ?? htmlspecialchars($value);
        },
        'created_at' => function($value) {
            return date('Y.m.d.', strtotime($value));
        }
    ],
    
    // Az összes táblabeli mező szerkeszthetővé tétele
    'form_fields' => [
        'seller_user_id', 'product_name', 'category', 'product_description', 
        'price', 'product_status', 'pickup_location', 'main_image_url'
    ],

    'fields' => [
        'seller_user_id' => [
            'label' => 'Eladó (Felhasználó)',
            'type' => 'select', 
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'users',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        'product_name' => ['label' => 'Termék neve', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'category' => ['label' => 'Kategória', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'product_description' => ['label' => 'Leírás', 'type' => 'textarea', 'required' => true, 'param_type' => 's'], 
        'price' => [
            'label' => 'Ár (HUF)', 
            'type' => 'number', 
            'step' => '1', 
            'required' => true, 
            'param_type' => 'd' // decimal miatt 'd' vagy 's' szerencsésebb, mint az 'i'
        ], 
        'product_status' => [ 
            'label' => 'Státusz', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 's', 
            'options' => [ 
                'active' => 'Aktív', 
                'sold' => 'Eladva (Elfogyott)', 
                'hidden' => 'Rejtett / Piszkozat' 
            ] 
        ],
        'pickup_location' => [
            'label' => 'Átvétel helyszíne', 
            'type' => 'text', 
            'placeholder' => 'Pl. Budapest, XI. kerület',
            'param_type' => 's'
        ],
        'main_image_url' => [
            'label' => 'Termék kép útvonala (vagy feltöltés)',
            'type' => 'text', // Ha a generic_crud csak text-et kezel le jópár helyen, a fájlfeltöltéshez külön logika kellhet
            'param_type' => 's',
            'default' => 'uploads/products/default_product.png'
        ] 
    ]
];

// 2. A CRUD sablon behívása
require_once ROOT_PATH . '/app/generic_crud.php';
?>