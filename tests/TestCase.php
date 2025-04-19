<?php

namespace OPGG\LaravelEssentialsEntry\Tests;

use CodeZero\BrowserLocale\BrowserLocale;
use CodeZero\LocalizedRoutes\LocalizedRoutesServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use OPGG\LaravelEssentialsEntry\LaravelEssentialsEntryServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $cookieName;

    /**
     * 테스트 환경을 설정합니다.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.key', Str::random(32));

        // 기본 브라우저 언어 설정 제거
        $this->setBrowserLocales(null);

        $this->cookieName = Config::get('essentials-entry.language.cookie.name');

        $this->defineEnvironment(app());
    }

    protected function setAcceptLanguage(string $locale)
    {
        $this->defaultHeaders['ACCEPT_LANGUAGE'] = $locale;
        App::bind(BrowserLocale::class, function () use ($locale) {
            return new BrowserLocale($locale);
        });
    }

    /**
     * 앱 로케일을 설정합니다.
     *
     * @param string $locale
     * @return void
     */
    protected function setAppLocale(string $locale): void
    {
        App::setLocale($locale);
    }

    protected function setCookieLocale(string $locale): void
    {
        $this->defaultCookies[$this->cookieName] = $locale;
    }

    /**
     * 지원되는 로케일을 설정합니다.
     *
     * @param array $locales
     * @return void
     */
    protected function setSupportedLocales(array $locales): void
    {
        Config::set('essentials-entry.language.supported', $locales);
        LaravelEssentialsEntryServiceProvider::overrideLocalizationConfig();
    }

    /**
     * 기본 로케일을 설정합니다.
     *
     * @param string|null $locale
     * @return void
     */
    protected function setFallbackLocale(?string $locale): void
    {
        Config::set('essentials-entry.language.default', $locale);
        LaravelEssentialsEntryServiceProvider::overrideLocalizationConfig();
    }

    protected function setLocaleMappings(array $mappings): void
    {
        Config::set('essentials-entry.language.locale_mappings', $mappings);
        LaravelEssentialsEntryServiceProvider::overrideLocalizationConfig();
    }

    /**
     * 세션에 로케일을 설정합니다.
     *
     * @param string $locale
     * @return void
     */
    protected function setSessionLocale(string $locale): void
    {
        Session::put($this->sessionKey, $locale);
    }

    /**
     * 브라우저 감지기에서 사용하는 로케일을 설정합니다.
     *
     * @param string|null $locales
     * @return void
     */
    protected function setBrowserLocales(?string $locales): void
    {
        App::bind(BrowserLocale::class, function () use ($locales) {
            return new BrowserLocale($locales);
        });
    }

    /**
     * 현재 등록된 라우트를 가져옵니다.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRoutes(): Collection
    {
        return new Collection(Route::getRoutes());
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function resolveApplicationHttpKernel($app): void
    {
        // In Laravel 6+, we need to add the middleware to
        // $middlewarePriority in Kernel.php for route
        // model binding to work properly.
        $app->singleton(
            'Illuminate\Contracts\Http\Kernel',
            'OPGG\LaravelEssentialsEntry\Tests\Stubs\Kernel'
        );
    }

    /**
     * 패키지 서비스 프로바이더를 로드합니다.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelEssentialsEntryServiceProvider::class,
            LocalizedRoutesServiceProvider::class,
        ];
    }

    /**
     * 패키지 aliases를 로드합니다.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app): array
    {
        return [
            'JsonLd' => 'OPGG\LaravelEssentialsEntry\Facades\JsonLd',
        ];
    }

    /**
     * 테스트 환경 설정을 정의합니다.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function defineEnvironment($app): void
    {
        // 기본 설정 파일 로드
        $config = require __DIR__ . '/../config/essentials-entry.php';
        $this->setConfigRecursive($config, 'essentials-entry');
        LaravelEssentialsEntryServiceProvider::overrideLocalizationConfig();
    }

    protected function setRedirectOptions(bool $browser = null, bool $cookie = null): void
    {
        if ($browser !== null) {
            Config::set('essentials-entry.language.redirect_to_accept_language_enabled', $browser);
        }
        if ($cookie !== null) {
            Config::set('essentials-entry.language.redirect_to_cookie_language_enabled', $cookie);
        }
        LaravelEssentialsEntryServiceProvider::overrideLocalizationConfig();
    }

    /**
     * 무한 다중차원 배열을 처리하는 메소드.
     *
     * @param array $config
     * @param string $key
     */
    private function setConfigRecursive(array $config, string $key): void
    {
        foreach ($config as $subKey => $subValue) {
            if (is_array($subValue) && array_is_list($subValue)) {
                Config::set("{$key}.{$subKey}", $subValue);
            } else if (is_array($subValue)) {
                $this->setConfigRecursive($subValue, "{$key}.{$subKey}");
            } else {
                if (is_string($subValue)) {
                    Config::set("{$key}.{$subKey}", $subValue);
                }
            }
        }
    }
}
