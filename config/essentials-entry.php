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
            $sitemap->add('/');

            // 언어별 경로 적용하기 - codezero-be/laravel-localized-routes 활용
            // $applyLocales는 route() 함수를 통해 다국어 URL 생성
            $applyLocales(function ($locale, $addUrlFromRoute) {
                // 라우트 이름과 파라미터를 활용한 URL 생성 예시
                // $addUrlFromRoute('user.profile', ['id' => 1]);
                // $addUrlFromRoute('products.show', ['product' => 'sample-product']);
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
    |
    | 이 설정은 opgginc/codezero-laravel-localized-routes 패키지를 사용하여 URL 경로에
    | 언어 코드를 포함하고 자동으로 언어를 감지합니다.
    |
    | 1. 미들웨어 등록 (bootstrap/app.php):
    | return Application::configure(basePath: dirname(__DIR__))
    |     ->withMiddleware(function (Middleware $middleware) {
    |         $middleware->web(remove: [
    |             \Illuminate\Routing\Middleware\SubstituteBindings::class,
    |         ]);
    |         $middleware->web(append: [
    |             \Illuminate\Routing\Middleware\SubstituteBindings::class,
    |             \OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage::class,
    |         ]);
    |     })
    |     ->create();
    |
    | 언어 감지 미들웨어는 SubstituteBindings 미들웨어 뒤에 배치해야 합니다.
    |
    | 2. 라우트 설정:
    | Route::localized(function () {
    |     Route::get('/', 'HomeController@index');
    | });
    |
    | 결과 URL 예시:
    | - /en/about      (영어)
    | - /ko_KR/about   (한국어)
    | - /zh_CN/about   (중국어)
    | - /about        (기본 언어일 경우)
    |
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
