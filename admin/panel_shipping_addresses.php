<?php
require '../app/auth_check.php';

$config = [
    // --- ALAPBEÁLLÍTÁSOK ---
    'table' => 'shipping_addresses',
    'pk' => 'address_id',
    'page_file' => '../admin/panel_shipping_address.php',
    'page_title' => 'Szállítási Címek',
    'singular_name' => 'cím',

    // --- LISTÁZÁS KONFIGURÁCIÓ (JOIN-nal a felhasználónévhez) ---
    'list_columns' => [
        'address_id' => 'ID',
        'username' => 'Felhasználó', // JOIN-nal töltjük be
        'zip_code' => 'Irsz.',
        'city' => 'Város',
        'street_address' => 'Utca',
        'phone_number' => 'Tel.',
        'is_billing_address' => 'Számlázási'
    ],
    
    // JOIN a users táblára, hogy a user_id helyett a username-et lássuk
    'list_query' => "SELECT s.*, u.username
                     FROM shipping_addresses s
                     JOIN users u ON s.user_id = u.user_id
                     ORDER BY s.address_id DESC",

    // Formázó a logikai mezőhöz (BOOLEAN)
    'list_formatters' => [
        'is_billing_address' => function ($value, $row) {
            return $value ? '✅ Igen' : '❌ Nem';
        }
    ],
    
    // --- ŰRLAP KONFIGURÁCIÓ ---
    'form_fields' => ['user_id', 'full_name', 'country', 'zip_code', 'city', 'street_address', 'phone_number', 'is_billing_address'],

    'fields' => [
        'address_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        'user_id' => [
            'label' => 'Felhasználó', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 'i',
            'foreign_key' => ['table' => 'users', 'value_col' => 'user_id', 'display_col' => 'username']
        ],
        'full_name' => ['label' => 'Teljes név', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'country' => ['label' => 'Ország', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'zip_code' => ['label' => 'Irányítószám', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'city' => ['label' => 'Város', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'street_address' => ['label' => 'Utca, házszám', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'phone_number' => ['label' => 'Telefonszám', 'type' => 'text', 'param_type' => 's'],
        'is_billing_address' => [
            'label' => 'Számlázási cím?', 
            'type' => 'checkbox', 
            'param_type' => 'i',
            'true_value' => 1,
            'false_value' => 0
        ],
    ]
];

require '../app/generic_crud.php';
?>