<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_articles.php

require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/app/auth_check.php';
require_once ROOT_PATH . '/app/helpers.php';

// --- CIKKEK (ARTICLES) KONFIGURÁCIÓ ---
$config = [
    'table' => 'articles',
    'pk' => 'article_id',
    'page_file' => BASE_URL . '/admin/admin?page=panel_articles',
    'page_title' => 'Cikkek',
    'singular_name' => 'cikk',

    'list_columns' => [
        'article_id' => 'ID',
        'cover_image' => 'Kép',
        'title' => 'Cím',
        'category_id' => 'Kategória',
        'author_user_id' => 'Szerző',
        'article_status' => 'Státusz',
        'created_at' => 'Dátum'
    ],

    'list_query' => "
        SELECT 
            a.*,
            u.username AS author_name,
            c.category_name
        FROM articles a
        LEFT JOIN users u ON a.author_user_id = u.user_id
        LEFT JOIN article_categories c ON a.category_id = c.category_id
        ORDER BY a.created_at DESC
    ",

    'list_formatters' => [
        'cover_image' => function($value) {
            $img = !empty($value) ? $value : 'uploads/articles/default_cover.png';
            $url = BASE_URL . '/' . $img;
            return '<img src="' . $url . '" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">';
        },
        'author_user_id' => function ($value, $row) {
            return htmlspecialchars($row['author_name'] ?? 'Ismeretlen');
        },
        'category_id' => function ($value, $row) {
            return htmlspecialchars($row['category_name'] ?? 'Nincs');
        },
        'article_status' => function ($value) {
            $colors = ['published' => 'green', 'draft' => 'orange', 'archived' => 'gray'];
            $labels = ['published' => 'Publikált', 'draft' => 'Vázlat', 'archived' => 'Archivált'];
            $color = $colors[$value] ?? 'black';
            $label = $labels[$value] ?? $value;
            return "<b style='color: $color;'>$label</b>";
        },
        'created_at' => function($value) {
            return date('Y.m.d.', strtotime($value));
        }
    ],

    'form_fields' => [
        'category_id', 'author_user_id', 'title', 'slug', 
        'summary', 'content', 'cover_image', 'reading_minutes', 'article_status'
    ],

    'preprocess_data' => function($data) {
        // Ellenőrizzük, hogy be van-e töltve a függvény (biztonsági játék)
        if (function_exists('make_slug')) {
            // Ha üres a slug, de van cím, generálunk egyet
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = make_slug($data['title']);
            } 
            // Ha a felhasználó írt be valamit, azt is "megtisztítjuk" (ékezetmentesítés, stb.)
            elseif (!empty($data['slug'])) {
                $data['slug'] = make_slug($data['slug']);
            }
        }
        
        return $data;
    },

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
        'title' => ['label' => 'Cím', 'type' => 'text', 'required' => true, 'param_type' => 's'],
        'slug' => ['label' => 'Slug (URL barát név)', 'type' => 'text', 'required' => false, 'param_type' => 's'],
        'summary' => ['label' => 'Összefoglaló (rövid)', 'type' => 'textarea', 'param_type' => 's'],
        'content' => ['label' => 'Cikk tartalma', 'type' => 'textarea', 'required' => true, 'param_type' => 's'],
        'cover_image' => [
            'label' => 'Borítókép útvonala',
            'type' => 'text',
            'default' => 'uploads/articles/default_cover.png',
            'param_type' => 's'
        ],
        'reading_minutes' => ['label' => 'Olvasási idő (perc)', 'type' => 'number', 'param_type' => 'i'],
        'article_status' => [
            'label' => 'Státusz',
            'type' => 'select',
            'required' => true,
            'param_type' => 's',
            'options' => [
                'draft' => 'Vázlat',
                'published' => 'Publikált',
                'archived' => 'Archivált'
            ]
        ]
    ]
];

require_once ROOT_PATH . '/app/generic_crud.php';