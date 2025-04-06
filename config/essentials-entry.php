<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Favicon Configuration
    |--------------------------------------------------------------------------
    */
    'favicon' => [
        'enabled' => true,
        'source_path' => null, // null이면 패키지 내부 파비콘 사용
        'path_rewrite' => '/favicon.ico',
        'cache' => [
            'enabled' => true,
            'duration' => 31536000, // 1년
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    */
    'sitemap' => [
        'enabled' => true,
        'schedule' => 60,
        'path_rewrite' => '/sitemap.xml',
        'cache_key' => 'essentials-entry.sitemap.xml',
        'generator' => function ($sitemap, $applyLocales) {
            // 여기서 사이트맵을 직접 구성합니다.
            // 기본 경로 추가 예시:
            $sitemap->add('/') // 홈페이지
                ->add('/about') // 소개 페이지
                ->add('/contact');

            // 언어별 경로 적용하기 - codezero-be/laravel-localized-routes 활용
            // $applyLocales는 route() 함수를 통해 다국어 URL 생성
            $applyLocales(function ($locale, $addUrlFromRoute) {
                // 라우트 이름과 파라미터를 활용한 URL 생성 예시
                $addUrlFromRoute('user.profile', ['id' => 1]);
                $addUrlFromRoute('products.show', ['product' => 'sample-product']);
            });

            return $sitemap;
        },
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
            'en',
            'ko_KR',
            'zh_CN',
            'zh_TW',
            'zh_HK',
            'es_ES',
        ],
        'cookie' => [
            'name' => '_ol',
            'minutes' => 60 * 24 * 365, // 1 year
        ],
    ],
];
