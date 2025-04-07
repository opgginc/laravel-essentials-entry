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
        // 중국어 홍콩/대만 버전 차이점은 https://kargn.as/posts/differences-hong-kong-taiwan-chinese-website-ui-localisation 참고
        'zh_HK' => 'zh_TW',
        'zh_TW' => 'zh_HK',
    ],
],
```

#### 언어 감지 메커니즘

본 패키지는 다음과 같은 순서와 우선순위로 사용자의 언어를 감지합니다:

1. **URL 경로 분석**: URL 경로에 언어 코드가 포함된 경우 이를 우선 사용
   - 예: `/ko_KR/about` 경로에서 `ko_KR` 감지

2. **쿠키 기반 감지**: 이전에 저장된 언어 설정이 있는 경우 해당 언어로 리다이렉트
   - 쿠키에 저장된 언어가 URL 경로와 다를 경우 쿠키 값 기준으로 리다이렉트

3. **브라우저 언어 감지**: 사용자 브라우저의 `Accept-Language` 헤더 분석
   - 경로에 언어가 없거나 기본 언어일 때 브라우저 언어가 다르면 해당 언어로 리다이렉트

4. **기본 언어 적용**: 위 모든 방법으로 언어를 감지하지 못한 경우 기본 언어 사용

##### 언어 매칭 알고리즘

`LocaleMatcher` 클래스는 브라우저나 쿠키에서 감지한 언어를 지원 언어와 매칭할 때 다음 순서로 처리합니다:

1. **정확한 일치**: 완전히 동일한 언어 코드 확인 (예: `ko_KR` → `ko_KR`)

2. **특별 매핑 확인**: `locale_mappings` 설정에 정의된 대체 가능 언어 확인
   - 예: `zh_HK`가 감지되었고 설정에 `'zh_HK' => 'zh_TW'`가 있으면 `zh_TW` 사용

3. **기본 언어 코드 매칭**: 언어 코드의 기본 부분만 일치하는지 확인
   - 예: 브라우저에서 `zh`가 감지되면 `supported` 배열에서 처음 나오는 `zh_` 로케일 선택
   - 이때 배열 순서가 중요: `zh_CN`이 `zh_TW`보다 먼저 정의되면 `zh` → `zh_CN` 매핑

4. **기본 언어 반환**: 모든 매칭이 실패하면 기본 언어(`default`) 사용

##### 실제 동작 예시

```
# 브라우저 언어가 'ko,en-US'이고 URL이 기본 도메인인 경우:
/ → /ko_KR/  (브라우저 언어 기반 리다이렉트)

# 사용자가 언어 전환 후 쿠키에 'zh_CN' 저장:
/ko_KR/about → /zh_CN/about  (다음 방문 시 쿠키 기반 리다이렉트)

# 브라우저 언어가 'zh-HK'이지만 'zh_HK'가 지원되지 않고 매핑된 경우:
/ → /zh_TW/  (특별 매핑 'zh_HK'=>'zh_TW' 적용, 중국어 홍콩/대만 차이점 참고: https://kargn.as/posts/differences-hong-kong-taiwan-chinese-website-ui-localisation)

# 브라우저 언어가 지원되지 않는 'ru'인 경우:
/ → /  (기본 언어 유지, 리다이렉트 없음)
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
- 변경한 언어코드를 위한 리다이렉트 기능

## Included Packages

- [opgginc/codezero-laravel-localized-routes](https://github.com/opgginc/codezero-laravel-localized-routes) - Create localized routes in Laravel
- [kargnas/laravel-ai-translator](https://github.com/kargnas/laravel-ai-translator) - AI-powered translation
- [spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap) - Generate sitemaps

## License

AGPL-3.0-only
