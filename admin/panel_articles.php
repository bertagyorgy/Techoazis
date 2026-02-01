<?php
require_once ROOT_PATH . '/app/auth_check.php';

// --- CIKKEK (ARTICLES) KONFIGURÁCIÓ ---
$config = [
    'table' => 'articles',
    'pk' => 'article_id',
    'page_file' => '../admin/panel_articles.php',
    'page_title' => 'Cikkek',
    'singular_name' => 'cikk',

    // ===== LISTA OSZLOPOK =====
    'list_columns' => [
        'article_id' => 'ID',
        'category_id' => 'Kategória',
        'author_user_id' => 'Szerző',
        'title' => 'Cím',
        'reading_minutes' => 'Olvasási idő (perc)',
        'status' => 'Státusz',
        'created_at' => 'Létrehozva'
    ],

    // ===== LISTA LEKÉRÉS =====
    'list_query' => "
        SELECT 
            a.*,
            u.username AS author_name,
            c.category_name
        FROM articles a
        JOIN users u ON a.author_user_id = u.user_id
        JOIN article_categories c ON a.category_id = c.category_id
        ORDER BY a.created_at DESC
    ",

    // ===== LISTA FORMÁZÁS =====
    'list_formatters' => [
        'author_user_id' => function ($value, $row) {
            return htmlspecialchars($row['author_name']);
        },
        'category_id' => function ($value, $row) {
            return htmlspecialchars($row['category_name']);
        },
        'status' => function ($value) {
            return ucfirst($value);
        }
    ],

    // ===== ŰRLAP MEZŐK =====
    'form_fields' => [
        'category_id',
        'author_user_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'reading_minutes',
        'status'
    ],

    // ===== MEZŐ DEFINÍCIÓK =====
    'fields' => [

        'category_id' => [
            'label' => 'Kategória',
            'type' => 'select',
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'article_categories',
                'value_col' => 'category_id',
                'display_col' => 'category_name'
            ]
        ],

        'author_user_id' => [
            'label' => 'Szerző',
            'type' => 'select',
            'required' => true,
            'param_type' => 'i',
            'foreign_key' => [
                'table' => 'users',
                'value_col' => 'user_id',
                'display_col' => 'username'
            ]
        ],

        'title' => [
            'label' => 'Cím',
            'type' => 'text',
            'required' => true
        ],

        'slug' => [
            'label' => 'Slug',
            'type' => 'text',
            'required' => true
        ],

        'summary' => [
            'label' => 'Összefoglaló',
            'type' => 'textarea',
            'required' => false
        ],

        'content' => [
            'label' => 'Tartalom',
            'type' => 'textarea',
            'required' => true
        ],

        'cover_image' => [
            'label' => 'Borítókép útvonal',
            'type' => 'text',
            'required' => false
        ],

        'reading_minutes' => [
            'label' => 'Olvasási idő (perc)',
            'type' => 'number',
            'required' => false,
            'param_type' => 'i'
        ],

        'status' => [
            'label' => 'Státusz',
            'type' => 'select',
            'required' => true,
            'options' => [
                'draft' => 'Vázlat',
                'published' => 'Publikált',
                'archived' => 'Archivált'
            ]
        ]
    ]
];

require '../app/generic_crud.php';
