<?php
require '../app/auth_check.php';

// --- RENDELÉSEK KONFIGURÁCIÓJA ---
$config = [
    'table' => 'orders',
    'pk' => 'order_id',
    'page_file' => '../admin/panel_orders.php',
    'page_title' => 'Rendelések',
    'singular_name' => 'rendelés',

    'list_columns' => [
        'order_id' => 'ID',
        'user_id' => 'Felhasználó', // Ezt a list_query felülírja
        'order_date' => 'Dátum',
        'total_amount' => 'Végösszeg',
        'order_status' => 'Státusz',
        'shipping_address_id' => 'Szállítási cím' // Visszaállítva címre
    ],
    
    // JOIN-olt lekérdezés: Visszaállítva az JOIN a KORREKT 'shipping_addresses' táblára.
    'list_query' => "
        SELECT 
            o.*, 
            u.username,
            sa.street_address AS shipping_street,
            sa.city AS shipping_city
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        LEFT JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id 
        ORDER BY o.order_date DESC
    ",

    'list_formatters' => [
        'user_id' => function($value, $row) {
            return htmlspecialchars($row['username'] ?? 'N/A');
        },
        'total_amount' => function($value, $row) {
            return number_format((float)$value, 2, ',', '.') . ' HUF';
        },
        'order_date' => function($value, $row) {
            return date('Y.m.d H:i', strtotime($value));
        },
        // Visszaállítva a cím megjelenítése
        'shipping_address_id' => function($value, $row) {
            $street = $row['shipping_street'] ?? 'Nincs cím';
            $city = $row['shipping_city'] ?? '';
            return htmlspecialchars($street . ', ' . $city);
        }
    ],

    'form_fields' => [
        'user_id', 'order_date', 'total_amount', 'shipping_cost', 'order_status', 
        'payment_method', 'transaction_id', 'shipping_address_id', 'billing_address_id'
    ],

    'fields' => [
        'order_id' => ['label' => 'ID', 'type' => 'number', 'param_type' => 'i', 'list_only' => true],
        
        'user_id' => [
            'label' => 'Felhasználó',
            'type' => 'select',
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'users',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],
        
        'order_date' => [
            'label' => 'Rendelés dátuma', 
            'type' => 'datetime-local', 
            'required' => false, 
            'param_type' => 's',
            'default' => date('Y-m-d\TH:i')
        ],
        
        'total_amount' => [
            'label' => 'Végösszeg', 
            'type' => 'number', 
            'step' => '0.01', 
            'required' => true, 
            'param_type' => 'd'
        ],
        
        'shipping_cost' => [
            'label' => 'Szállítási költség', 
            'type' => 'number', 
            'step' => '0.01', 
            'required' => true, 
            'param_type' => 'd',
            'default' => 0.00
        ],
        
        'order_status' => [
            'label' => 'Státusz', 
            'type' => 'select', 
            'required' => true, 
            'param_type' => 's',
            'options' => [
                'Függőben' => 'Függőben',
                'Feldolgozás alatt' => 'Feldolgozás alatt',
                'Elküldve' => 'Elküldve',
                'Kézbesítve' => 'Kézbesítve',
                'Törölve' => 'Törölve'
            ]
        ],
        
        'payment_method' => [
            'label' => 'Fizetési mód', 
            'type' => 'text', 
            'required' => false, 
            'param_type' => 's'
        ],
        
        'transaction_id' => [
            'label' => 'Tranzakció ID', 
            'type' => 'text', 
            'required' => false, 
            'param_type' => 's'
        ],
        
        // Visszaállítva 'select' típusra a KORREKT táblanévvel
        'shipping_address_id' => [
            'label' => 'Szállítási cím ID',
            'type' => 'select', // VISSZAÁLLÍTVA
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'shipping_addresses', // JAVÍTVA
                'value_col' => 'address_id',
                'display_col' => 'street_address' // Új display oszlop
            ]
        ],
        
        'billing_address_id' => [
            'label' => 'Számlázási cím ID',
            'type' => 'select', // VISSZAÁLLÍTVA
            'required' => false,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'shipping_addresses', // JAVÍTVA
                'value_col' => 'address_id',
                'display_col' => 'street_address' // Új display oszlop
            ]
        ]
    ]
];

require '../app/generic_crud.php';
?>