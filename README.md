# Laravel Essentials Entry

Essential Laravel modules collection for Entry team.

## Requirements

- PHP ^8.4
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

자동으로 사이트맵을 생성하고 스케줄링할 수 있습니다.

```bash
# 수동으로 사이트맵 생성
php artisan essentials:generate-sitemap
```

#### 설정

`config/essentials-entry.php` 파일에서 사이트맵 설정을 변경할 수 있습니다:

```php
'sitemap' => [
    'enabled' => Config::get('app.sitemap_enabled', true),
    'path' => Storage::path('public/sitemap.xml'),
    'schedule' => Config::get('app.sitemap_schedule', 60), // 분 단위
    'domain' => Config::get('app.url', 'http://localhost'),
    'routes' => [
        '/',
        '/about',
        '/contact',
    ],
    'defaults' => [
        'changefreq' => 'daily',
        'priority' => 0.8,
    ],
    'exclude' => [
        '/admin/*',
        '/api/*',
    ],
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

### 3. 언어 감지 및 설정

다양한 방식으로 사용자의 언어를 감지하고 설정할 수 있습니다.

#### 미들웨어 등록

`app/Http/Kernel.php` 파일에 미들웨어를 등록하세요:

```php
protected $middlewareAliases = [
    // ...
    'detect-language' => \OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage::class,
];
```

#### 설정

`config/essentials-entry.php` 파일에서 언어 설정을 변경할 수 있습니다:

```php
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
    // ...
],
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
- [codezero/laravel-localized-routes](https://github.com/codezero-be/laravel-localized-routes) - Create localized routes in Laravel
- [kargnas/laravel-ai-translator](https://github.com/kargnas/laravel-ai-translator) - AI-powered translation
- [spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap) - Generate sitemaps

## License

MIT
