<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Favicon Configuration
    |--------------------------------------------------------------------------
    | enabled 를 true 로 설정하면 아래 Meta Tag 설정으로 자동으로 favicon 이 삽입됩니다.
    */
    'favicon' => [
        'enabled' => true,
        'source_path' => null, // null이면 op.gg 파란색 기본 파비콘 사용
        'path_rewrite' => 'favicon.ico',
        'cache' => [
            'enabled' => true,
            'duration' => 31536000, // 1년
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Tags Configuration
    |--------------------------------------------------------------------------
    | app.blade.php 에서 <title>Laravel</title> 대신 {!! Meta::toHtml() !!}를 하면
    | 메타 태그가 자동으로 들어갑니다.
    */
    'meta-tags' => [
        'og' => [
            'type' => 'website',
            'image' => 'https://s-lol-web.op.gg/images/reverse.rectangle.png',
        ],
        'images' => [
            'touch_icon' => '/apple-touch-icon.png',
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
        'path_rewrite' => 'sitemap.xml',
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
    | - /ko/about      (한국어)
    | - /zh-cn/about   (중국어 간체)
    | - /zh-tw/about   (중국어 번체 대만)
    | - /about         (기본 언어일 경우)
    |
    */
    'language' => [
        'enabled' => true,

        // 접근한 페이지와 다른 언어 값으로 페이지를 접근했을 때 자동 이동할 것인지
        'redirect_to_accept_language_enabled' => false,
        'redirect_to_cookie_language_enabled' => true,

        // 기본 언어, 언어 값이 없이 루트로 URL이 구성됨
        'default' => env('APP_LOCALE'),

        // 지원 언어 목록 (순서가 중요: 같은 기본 언어에 대해 먼저 나열된 항목이 우선순위가 높음)
        // 예: 'zh'를 감지하면 아래 순서대로 'zh_CN'이 'zh_TW'나 'zh_HK'보다 우선 적용됨
        'supported' => [
            'en',
            'es',
            'zh-cn',  // 중국어 간체 우선
            'zh-tw',  // 번체 (대만)
            // 'zh-hk',  // 번체 (홍콩)
            'ja',
            'ko',
        ],
        'cookie' => [
            'name' => '_ol',
            'minutes' => 60 * 24 * 365, // 1 year
        ],
        // 특별 매핑 관계 (서로 대체 가능한 언어들)
        'locale_mappings' => [
            // 번체 중국어 상호 참조 (zh_HK와 zh_TW는 모두 번체 중국어라 서로 대체 가능)
            // 중국어 홍콩/대만 버전 차이점은 https://kargn.as/posts/differences-hong-kong-taiwan-chinese-website-ui-localisation 참고
            'zh-hk' => 'zh-tw',
            'zh-tw' => 'zh-hk',
        ],
    ],
];
