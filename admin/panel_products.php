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
        'user_id' => 'Feltöltő', // Ezt a list_query felülírja 'username'-re
        'name' => 'Név',
        'category' => 'Kategória',
        'price' => 'Ár',
        'stock' => 'Készlet'
    ],
    
    // Egyéni JOIN-olt lekérdezés, hogy a felhasználónevet lássuk az ID helyett
    'list_query' => "SELECT p.*, u.username 
                     FROM products p 
                     JOIN users u ON p.user_id = u.user_id 
                     ORDER BY p.product_id",
                     
    // A 'list_columns'-ban lévő 'user_id' kulcsot a 'username' oszlopra cseréljük
    'list_formatters' => [
        'user_id' => function($value, $row) {
            return htmlspecialchars($row['username']); // A $row a teljes sor a list_query-ből
        }
    ],
    // Mivel a list_query betölti a 'username'-t, a formatter felül tudja írni a 'user_id' kijelzését
    // Egyszerűbb megoldás:
    // 'list_columns' => ['product_id' => 'ID', 'username' => 'Feltöltő', ... ]
    // A fenti formattert törölheted, ha a list_columns kulcsa megegyezik a list_query oszlopnevével
    
    // Mezők a "Hozzáadás" és "Szerkesztés" űrlapokon
    'form_fields' => ['user_id', 'product_name', 'category', 'product_description', 'price', 'stock'],

    // Részletes meződefiníciók az űrlaphoz
    'fields' => [
        'user_id' => [
            'label' => 'Feltöltő felhasználó',
            'type' => 'select', // --- EZ A LEGÖRDÜLŐ MENÜ ---
            'required' => true,
            'param_type' => 'i', // integer
            'foreign_key' => [
                'table' => 'USERS',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        'name' => ['label' => 'Termék neve', 'type' => 'text', 'required' => true],
        'category' => ['label' => 'Kategória', 'type' => 'text'],
        'description' => ['label' => 'Leírás', 'type' => 'textarea'],
        'price' => ['label' => 'Ár', 'type' => 'number', 'step' => '0.01', 'required' => true, 'param_type' => 'd'], // double
        'stock' => ['label' => 'Készlet', 'type' => 'number', 'default' => 0, 'param_type' => 'i'] // integer
    ]
];

// --- SABLON BETÖLTÉSE ---
require '../app/generic_crud.php';
?>