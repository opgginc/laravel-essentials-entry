<?php

namespace OPGG\LaravelEssentialsEntry;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use OPGG\LaravelEssentialsEntry\Config\ConfigValidator;
use OPGG\LaravelEssentialsEntry\Console\Commands\GenerateRobots;
use OPGG\LaravelEssentialsEntry\Console\Commands\GenerateSitemap;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Providers\MetaTagsServiceProvider;
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
            App::basePath('config/essentials-entry.php'),
            'essentials-entry'
        );

        // 설정 유효성 검사
        try {
            ConfigValidator::validate();
        } catch (\InvalidArgumentException $e) {
            // 개발 환경에서만 예외 발생
            if (Config::get('app.debug')) {
                throw $e;
            }
        }

        // 메타 태그 서비스 프로바이더 등록
        $this->app->register(MetaTagsServiceProvider::class);

        // 파사드 등록
        $this->app->bind('essentials-entry.json-ld', function () {
            return new \OPGG\LaravelEssentialsEntry\Schema\JsonLd('WebSite');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Config 파일 publish
        $this->publishes([
            App::basePath('config/essentials-entry.php') => App::basePath('config/essentials-entry.php'),
        ], 'essentials-entry-config');

        // 미들웨어 등록
        $this->app['router']->aliasMiddleware('detect-language', DetectLanguage::class);

        // 명령어 등록
        if ($this->app->runningInConsole()) {
            $this->commands([
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
}
