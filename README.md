# Laravel Essentials Entry

Essential Laravel modules collection for Entry team.

## Requirements

- PHP ^8.3
- Laravel 10.x, 11.x, 12.x

## Installation

```bash
composer require opgg/laravel-essentials-entry
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

### 2. 메타 태그 관리

Inertia.js와 통합된 메타 태그 관리 기능을 제공합니다.

#### 설정

`config/essentials-entry.php` 파일에서 메타 태그 설정을 변경할 수 있습니다:

```php
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
    // ...
],
```

#### 사용법

블레이드 템플릿에서:

```php
{!! preg_replace('/<title>(.*?)<\/title>/', '<title inertia>$1</title>', Meta::toHtml()) !!}
```

### 3. 다국어 URL 설정

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
    'default' => 'en',      // 기본 언어 (이 언어는 URL에 표시되지 않음)
    'supported' => [        // 지원하는 언어 목록
        'en',
        'ko_KR',
        'zh_CN',
        'zh_TW',
        'es_ES',
    ],
    'cookie' => [           // 사용자 언어 설정 저장용 쿠키
        'name' => '_ol',
        'minutes' => 60 * 24 * 365, // 1년
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

### 4. 캐시 관리

메타 태그와 사이트맵에 대한 캐시 관리 기능을 제공합니다.

```php
'cache' => [
    'enabled' => Config::get('app.meta_tags_cache_enabled', true),
    'duration' => Config::get('app.meta_tags_cache_duration', 3600), // 초 단위
],
```

### 5. 로봇 차단 파일(robots.txt) 생성

사이트맵과 함께 robots.txt 파일도 자동으로 생성할 수 있습니다.

```bash
php artisan essentials:generate-robots
```

### 6. JSON-LD 스키마 지원

구조화된 데이터를 위한 JSON-LD 스키마를 추가할 수 있습니다.

```php
use OPGG\LaravelEssentialsEntry\Schema\JsonLd;

// 제품 스키마 생성
$schema = JsonLd::product()
    ->name('제품명')
    ->description('제품 설명')
    ->price(10000)
    ->priceCurrency('KRW')
    ->toScript();

// 블레이드 템플릿에서 출력
{!! $schema !!}
```

### 7. 다국어 URL 관리

다국어 URL 구조를 관리할 수 있습니다.

```php
// routes/web.php
Route::localized(function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/about', 'AboutController@index')->name('about');
});
```

## 테스트

```bash
composer test
```

## Included Packages

- [butschster/meta-tags](https://github.com/butschster/meta-tags) - Manage meta tags, SEO optimization
- [opgginc/codezero-laravel-localized-routes](https://github.com/opgginc/codezero-laravel-localized-routes) - Create localized routes in Laravel
- [kargnas/laravel-ai-translator](https://github.com/kargnas/laravel-ai-translator) - AI-powered translation
- [spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap) - Generate sitemaps

## License

MIT
