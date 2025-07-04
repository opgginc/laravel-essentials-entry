<?php

namespace OPGG\LaravelEssentialsEntry;

use CodeZero\LocalizedRoutes\LocalizedRoutesServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Console\Commands\CleanLanguageFilesCommand;
use OPGG\LaravelEssentialsEntry\Console\Commands\GenerateRobots;
use OPGG\LaravelEssentialsEntry\Console\Commands\GenerateSitemap;
use OPGG\LaravelEssentialsEntry\Http\Controllers\FaviconController;
use OPGG\LaravelEssentialsEntry\Http\Controllers\SitemapController;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Providers\MetaTagsServiceProvider;
use OPGG\LaravelEssentialsEntry\Support\EssentialsEntry;
use Illuminate\Console\Scheduling\Schedule;

class LaravelEssentialsEntryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Config 파일 등록
        $this->mergeConfigFrom(
            __DIR__ . '/../config/essentials-entry.php',
            'essentials-entry'
        );

        // localized-routes.php 파일 존재 여부 확인
        if (file_exists(config_path('localized-routes.php'))) {
            throw new \Exception(
                "[OPGG Essentials Entry] localized-routes.php 파일이 존재합니다. " .
                "essentials-entry 패키지의 언어 설정과 충돌을 방지하기 위해 해당 파일을 삭제해주세요."
            );
        }

        // codezero-laravel-localized-routes 설정 등록
        static::overrideLocalizationConfig();

        // 메타 태그 서비스 프로바이더 등록
        $this->app->register(MetaTagsServiceProvider::class);

        // 파사드 등록
        $this->app->bind('essentials-entry.json-ld', function () {
            return new \OPGG\LaravelEssentialsEntry\Schema\JsonLd('WebSite');
        });

        // EssentialsEntry 서비스 등록
        $this->app->singleton('essentials-entry', function () {
            return new EssentialsEntry();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Config 파일 publish
        $this->publishes([
            __DIR__ . '/../config/essentials-entry.php' => $this->app->configPath('essentials-entry.php'),
        ], 'essentials-entry-config');

        // 모든 설정에 대한 기본값 설정
        $this->setDefaultOptions();

        // 사이트맵 라우트 등록
        $this->registerSitemapRoutes();

        // 파비콘 라우트 등록
        $this->registerFaviconRoute();

        // 명령어 등록
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanLanguageFilesCommand::class,
                GenerateSitemap::class,
                GenerateRobots::class,
            ]);

            // 스케줄러 등록
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $config = Config::get('essentials-entry.sitemap');
                if ($config['enabled']) {
                    $schedule->command('essentials:generate-sitemap')
                        ->cron("*/{$config['schedule']} * * * *");

                    $schedule->command('essentials:generate-robots')
                        ->cron("*/{$config['schedule']} * * * *");
                }
            });
        }
    }

    /**
     * 사이트맵 라우트를 등록합니다.
     */
    protected function registerSitemapRoutes(): void
    {
        $config = Config::get('essentials-entry.sitemap', []);

        if (!empty($config['enabled']) && !empty($config['path_rewrite'])) {
            Route::get($config['path_rewrite'], [SitemapController::class, 'index']);
        }
    }

    /**
     * 파비콘 라우트를 등록합니다.
     */
    protected function registerFaviconRoute(): void
    {
        $config = Config::get('essentials-entry.favicon', []);

        if (!empty($config['enabled']) && !empty($config['path_rewrite'])) {
            Route::get($config['path_rewrite'], [FaviconController::class, 'index']);
        }
    }

    /**
     * 모든 설정의 기본값 설정
     */
    private function setDefaultOptions(): void
    {
        $defaultOptions = [
            // 2025.04.19
            'language.redirect_to_accept_language_enabled' => false,
            'language.redirect_to_cookie_language_enabled' => true,
        ];

        // 각 섹션별로 기본값 적용
        foreach ($defaultOptions as $key => $value) {
            if (!Config::has('essentials-entry.' . $key)) {
                Config::set('essentials-entry.' . $key, $value);
            }
        }
    }

    /**
     * codezero-laravel-localized-routes 설정 덮어쓰기
     */
    public static function overrideLocalizationConfig(): void
    {
        $config = Config::get('essentials-entry.language');
        if (!$config['enabled']) {
            return;
        }

        // localized-routes 설정이 있는지 확인
        if (!file_exists(config_path('localized-routes.php'))) {
            // 우선순위를 유지하면서 중복 제거
            // $detectionMethods = array_values(array_unique($config['detect_from']));

            Config::set('localized-routes.supported_locales', ($config['supported']));
            Config::set('localized-routes.fallback_locale', $config['default']);
            Config::set('localized-routes.omitted_locale', $config['default']);
            // Config::set('localized-routes.redirect_to_localized_urls', true);
            Config::set('localized-routes.detectors', [
                \CodeZero\LocalizedRoutes\Middleware\Detectors\RouteActionDetector::class, //=> required for scoped config
                \CodeZero\LocalizedRoutes\Middleware\Detectors\UrlDetector::class, //=> required
                \CodeZero\LocalizedRoutes\Middleware\Detectors\OmittedLocaleDetector::class, //=> required for omitted locale
                // \CodeZero\LocalizedRoutes\Middleware\Detectors\UserDetector::class,
                // \CodeZero\LocalizedRoutes\Middleware\Detectors\SessionDetector::class,
                // \CodeZero\LocalizedRoutes\Middleware\Detectors\CookieDetector::class,
                // \CodeZero\LocalizedRoutes\Middleware\Detectors\BrowserDetector::class,
                \CodeZero\LocalizedRoutes\Middleware\Detectors\AppDetector::class, //=> required
            ]);
            Config::set('localized-routes.check_raw_cookie', true);
            Config::set('localized-routes.cookie_name', $config['cookie']['name']);
            Config::set('localized-routes.cookie_minutes', $config['cookie']['minutes']);
            Config::set('localized-routes.stores', [
                \CodeZero\LocalizedRoutes\Middleware\Stores\AppStore::class, //=> required
            ]);
        }
    }
}
