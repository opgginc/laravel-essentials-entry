<?php

namespace OPGG\LaravelEssentialsEntry\Routing;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class LocalizedRoutes
{
    /**
     * 다국어 라우트 그룹을 생성합니다.
     *
     * @param  callable  $callback
     * @param  array  $options
     * @return void
     */
    public static function group(callable $callback, array $options = []): void
    {
        $config = Config::get('essentials-entry.language');
        
        if (!$config['enabled']) {
            $callback();
            return;
        }
        
        $supportedLocales = array_keys($config['supported']);
        $defaultLocale = $config['default'];
        
        // 기본 로케일 라우트 (접두사 없음)
        Route::group([], function () use ($callback, $defaultLocale) {
            App::setLocale($defaultLocale);
            $callback();
        });
        
        // 다국어 라우트 (로케일 접두사 포함)
        foreach ($supportedLocales as $locale) {
            if ($locale === $defaultLocale && ($options['skip_default_locale'] ?? true)) {
                continue;
            }
            
            Route::prefix($locale)
                ->middleware(['detect-language'])
                ->group(function () use ($callback, $locale) {
                    App::setLocale($locale);
                    $callback();
                });
        }
    }
    
    /**
     * 현재 로케일로 URL을 생성합니다.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */
    public static function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $locale = App::getLocale();
        $config = Config::get('essentials-entry.language');
        $defaultLocale = $config['default'];
        
        // 기본 로케일이 아닌 경우 로케일 접두사 추가
        if ($locale !== $defaultLocale) {
            $name = "{$locale}.{$name}";
        }
        
        return route($name, $parameters, $absolute);
    }
    
    /**
     * 특정 로케일로 URL을 생성합니다.
     *
     * @param  string  $locale
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */
    public static function localeRoute(string $locale, string $name, array $parameters = [], bool $absolute = true): string
    {
        $config = Config::get('essentials-entry.language');
        $defaultLocale = $config['default'];
        
        // 지원하지 않는 로케일인 경우 기본 로케일 사용
        if (!isset($config['supported'][$locale])) {
            $locale = $defaultLocale;
        }
        
        // 기본 로케일이 아닌 경우 로케일 접두사 추가
        if ($locale !== $defaultLocale) {
            $name = "{$locale}.{$name}";
        }
        
        return route($name, $parameters, $absolute);
    }
    
    /**
     * 모든 지원 로케일에 대한 URL 목록을 생성합니다.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return array
     */
    public static function allLocaleRoutes(string $name, array $parameters = [], bool $absolute = true): array
    {
        $config = Config::get('essentials-entry.language');
        $supportedLocales = $config['supported'];
        $routes = [];
        
        foreach (array_keys($supportedLocales) as $locale) {
            $routes[$locale] = static::localeRoute($locale, $name, $parameters, $absolute);
        }
        
        return $routes;
    }
}
