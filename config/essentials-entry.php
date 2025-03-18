<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

return [
    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    */
    'sitemap' => [
        'enabled' => Config::get('app.sitemap_enabled', true),
        'path' => Storage::path('public/sitemap.xml'),
        'schedule' => Config::get('app.sitemap_schedule', 60),
        'domain' => Config::get('app.url', 'http://localhost'),
        'routes' => [
            '/',
            '/about',
            '/contact',
        ],
        'defaults' => [
            'changefreq' => 'daily',
            'priority' => 0.8,
            'lastmod' => null,
        ],
        'exclude' => [
            '/admin/*',
            '/api/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Tags Configuration
    |--------------------------------------------------------------------------
    */
    'meta-tags' => [
        'defaults' => [
            'title' => Config::get('app.name', 'Laravel'),
            'description' => '',
            'keywords' => '',
            'robots' => 'index,follow',
            'viewport' => 'width=device-width, initial-scale=1',
            'charset' => 'utf-8',
        ],
        'og' => [
            'site_name' => Config::get('app.name', 'Laravel'),
            'type' => 'website',
            'image' => '/images/og-image.jpg',
        ],
        'twitter' => [
            'card' => 'summary_large_image',
            'site' => '@yoursite',
            'creator' => '@yourname',
        ],
        'cache' => [
            'enabled' => Config::get('app.meta_tags_cache_enabled', true),
            'duration' => Config::get('app.meta_tags_cache_duration', 3600),
        ],
        'images' => [
            'favicon' => '/favicon.ico',
            'touch_icon' => '/apple-touch-icon.png',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Detection Configuration
    |--------------------------------------------------------------------------
    */
    'language' => [
        'enabled' => true,
        'default' => 'en',
        'supported' => [
            'en' => 'English',
            'ko' => 'Korean',
            'ja' => 'Japanese',
            'zh' => 'Chinese',
            'es' => 'Spanish',
        ],
        'detect_from' => [
            'cookie',
            'browser',
            'subdomain',
            'path',
        ],
        'cookie' => [
            'name' => 'locale',
            'duration' => 43200, // 30 days
        ],
        'carbon_mapping' => [
            'ko_KR' => 'ko',
            'ja_JP' => 'ja',
            'zh_CN' => 'zh',
            'zh_TW' => 'zh',
            'es_ES' => 'es',
            'en' => 'en',
        ],
        'locale_mapping' => [
            'ko' => 'ko_KR',
            'ja' => 'ja_JP',
            'zh' => 'zh_CN',
            'es' => 'es_ES',
            'en' => 'en',
        ],
    ],
];
