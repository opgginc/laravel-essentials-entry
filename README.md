# Laravel Essentials Entry

Essential Laravel modules collection for Entry team.

## Requirements

- PHP ^8.3
- Laravel 10.x, 11.x, 12.x

## Installation

```bash
composer require opgginc/laravel-essentials-entry
```

설정 파일을 게시하려면:

```bash
php artisan vendor:publish --tag=essentials-entry-config
```

## 기능

### 1. 사이트맵 생성

이 패키지를 설치하면 자동으로 사이트맵을 생성하고 스케줄링됩니다. 단, 어떻게 사이트맵을 생성할 것인지 설정 파일에서 잘 설정해주어야합니다.

> 주의: 이 기능은 Cache 에 저장한 후에 웹에서 서빙할 수 있도록 해줍니다. 사이트맵 파일이 너무 커지는 경우, 캐시에 담을 수 없을 경우도 생길 수 있습니다. 그러면 이 기능을 비활성화하고 직접 sitemap.xml 을 처음부터 구현하시기 바랍니다.

```bash
# 수동으로 사이트맵 생성 테스트
php artisan essentials:generate-sitemap
```

#### 설정

`config/essentials-entry.php` 파일에서 사이트맵 설정을 변경할 수 있습니다:

```php
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
```

### 2. 사이트 언어 관리 (다국어 URL 설정)

[opgginc/codezero-laravel-localized-routes](https://github.com/opgginc/codezero-laravel-localized-routes) 패키지를 사용하여 URL 경로에 언어 코드를 포함하고 자동으로 언어를 감지합니다. 이 패키지는 `codezero/laravel-localized-routes`의 포크 버전으로, 원본 패키지의 관리자 사망으로 인해 유지보수가 중단된 후 자체적으로 관리하고 있는 버전입니다.

#### 미들웨어 등록

`bootstrap/app.php` 파일에 미들웨어를 등록하세요:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        // 언어 감지 미들웨어 등록
        $middleware->web(remove: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->web(append: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage::class,
        ]);
    })
    ->create();
```

언어 감지 미들웨어는 `SubstituteBindings` 미들웨어 뒤에 배치해야 합니다.

#### 라우트 설정

모든 다국어 라우트는 `Route::localized()` 내부에 정의해야 합니다:

```php
// routes/web.php
Route::localized(function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/about', 'AboutController@index')->name('about');
});

// 결과 URL 예시:
// - /en/about      (영어)
// - /ko_KR/about   (한국어)
// - /zh_CN/about   (중국어)
// - /about        (기본 언어일 경우)
```

#### 설정

`config/essentials-entry.php` 파일에서 지원할 언어와 기본 언어를 설정할 수 있습니다:

```php
'language' => [
    'enabled' => true,
    'default' => env('APP_LOCALE'),  // 기본 언어 (이 언어는 URL에 표시되지 않음)
    // 지원 언어 목록 (순서가 중요: 같은 기본 언어에 대해 먼저 나열된 항목이 우선순위가 높음)
    // 예: 'zh'를 감지하면 아래 순서대로 'zh_CN'이 'zh_TW'나 'zh_HK'보다 우선 적용됨
    'supported' => [
        'en',
        'es_ES',
        'zh_CN',  // 중국어 간체 우선
        'zh_TW',  // 번체 (대만)
        // 'zh_HK',  // 번체 (홍콩)
        'ja_JP',
        'ko_KR',
    ],
    'cookie' => [           // 사용자 언어 설정 저장용 쿠키
        'name' => '_ol',
        'minutes' => 60 * 24 * 365, // 1년
    ],
    // 특별 매핑 관계 (서로 대체 가능한 언어들)
    'locale_mappings' => [
        // 번체 중국어 상호 참조 (zh_HK와 zh_TW는 모두 번체 중국어라 서로 대체 가능)
        'zh_HK' => 'zh_TW',
        'zh_TW' => 'zh_HK',
    ],
],
```

#### 라우트 생성 예시

```php
// 다국어 라우트 생성
Route::localized(function () {
    // 모든 언어에 대해 다음 URL들이 생성됨:
    // - /{locale}/
    // - /{locale}/users
    // - /{locale}/users/{id}
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/users', 'UserController@index')->name('users.index');
    Route::get('/users/{id}', 'UserController@show')->name('users.show');
});

// 라우트 URL 생성
$url = route('users.show', ['id' => 1, 'locale' => 'ko_KR']); // /ko_KR/users/1
```

### 3. 로봇 차단 파일(robots.txt) 생성

사이트맵과 함께 robots.txt 파일도 자동으로 생성할 수 있습니다.

```bash
php artisan essentials:generate-robots
```

## 테스트

```bash
composer test
```

## TODO LIST

다음 기능들은 향후 구현 예정입니다:

- 메타 태그 관리: Inertia.js와 통합된 메타 태그 관리 기능
- JSON-LD 스키마 지원: 구조화된 데이터를 위한 JSON-LD 스키마 추가

## Included Packages

- [opgginc/codezero-laravel-localized-routes](https://github.com/opgginc/codezero-laravel-localized-routes) - Create localized routes in Laravel
- [kargnas/laravel-ai-translator](https://github.com/kargnas/laravel-ai-translator) - AI-powered translation
- [spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap) - Generate sitemaps

## License

MIT
