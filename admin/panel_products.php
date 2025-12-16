<?php
require '../app/auth_check.php'; // Adatbázis $conn és authentikáció

// --- TERMÉKEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'PRODUCTS',
    'pk' => 'product_id',
    'page_file' => '../admin/panel_products.php',
    'page_title' => 'Termékek',
    'singular_name' => 'termék',

    // Oszlopok a listázó nézetben
    'list_columns' => [
        'product_id' => 'ID',
        'user_id' => 'Feltöltő', 
        'product_name' => 'Név',
        'category' => 'Kategória',
        'price' => 'Ár',
        'stock_quantity' => 'Készlet', // JAVÍTVA: stock helyett stock_quantity
        'main_image_url' => 'Kép', 
    ],
    
    // Egyéni JOIN-olt lekérdezés, hogy a felhasználónevet lássuk az ID helyett
    'list_query' => "SELECT p.*, u.username 
                     FROM products p 
                     JOIN users u ON p.seller_user_id = u.user_id 
                     ORDER BY p.product_id",
                     
    // A 'list_columns'-ban lévő kulcsok formázása
    'list_formatters' => [
        'user_id' => function($value, $row) {
            return htmlspecialchars($row['username']); 
        },
        'price' => function($value, $row) {
            // Ár formázása
            return number_format((float)$value, 0, '', ' ') . ' HUF';
        },
        // Kép megjelenítése miniatűrként a listában
        'main_image_url' => function($value, $row) {
             if (empty($value)) {
                 return 'Nincs kép';
             }
             // FIX: Az admin könyvtárból a gyökérben lévő images/ mappára mutatunk.
             $image_path = '../uploads/products/' . htmlspecialchars($value);
             // Egy kis miniatűr a könnyebb azonosításhoz
             return '<img src="' . $image_path . '" alt="Termékkép" style="max-width: 50px; height: auto; border-radius: 4px;">';
        }
    ],
    
    // Mezők a "Hozzáadás" és "Szerkesztés" űrlapokon
    // JAVÍTVA: stock helyett stock_quantity - ez kritikus a generic_crud.php működéséhez
    'form_fields' => ['user_id', 'product_name', 'category', 'product_description', 'price', 'stock_quantity', 'main_image_url'],

    // Részletes meződefiníciók az űrlaphoz. Itt az adatbázis oszlop neve a kulcs!
    'fields' => [
        'product_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'user_id' => [
            'label' => 'Feltöltő felhasználó',
            'type' => 'select', 
            'required' => true,
            'param_type' => 'i', // integer
            'foreign_key' => [
                'table' => 'USERS',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        'product_name' => ['label' => 'Termék neve', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'category' => ['label' => 'Kategória', 'type' => 'text', 'param_type' => 's'],
        'product_description' => ['label' => 'Leírás', 'type' => 'textarea', 'param_type' => 's'], 
        
        // Ár (egész számra van állítva)
        'price' => ['label' => 'Ár', 'type' => 'number', 'step' => '1', 'required' => true, 'param_type' => 'i'], 
        
        // Készlet: A kulcs most már megegyezik a fent használt kulcsokkal és a helyes adatbázis oszlopnévvel.
        'stock_quantity' => ['label' => 'Készlet', 'type' => 'number', 'default' => 10, 'param_type' => 'i'], 
        
        // Kép feltöltő mező.
        'main_image_url' => [
            'label' => 'Termék fő képe (Képfeltöltés)',
            'type' => 'file', 
            'param_type' => 's', // A mentett adat (az elérési út) string
        ] 
    ]
];

// --- SABLON BETÖLTÉSE ---
require '../app/generic_crud.php';