<?php

namespace OPGG\LaravelEssentialsEntry\Http\Middleware;

use Carbon\Carbon;
use Closure;
use CodeZero\BrowserLocale\BrowserLocale;
use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use CodeZero\LocalizedRoutes\Middleware\Detectors\BrowserDetector;
use CodeZero\LocalizedRoutes\Middleware\Detectors\CookieDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use CodeZero\LocalizedRoutes\Middleware\LocaleHandler;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Support\LocaleMatcher;

class DetectLanguage
{
    /**
     * LocaleHandler.
     *
     * @var \CodeZero\LocalizedRoutes\Middleware\LocaleHandler
     */
    protected $handler;

    /**
     * DetectLanguage constructor.
     *
     * @param  \CodeZero\LocalizedRoutes\Middleware\LocaleHandler  $handler
     * @return void
     */
    public function __construct(LocaleHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $config = Config::get('essentials-entry.language');
        if (!$config['enabled']) {
            \Log::warn('DetectLanguage middleware 가 호출되었지만, 언어 설정이 비활성화된 상태입니다. (essentials-entry.language.enabled = false)');
            return $next($request);
        }

        $pathLocale = $this->handler->detect();

        $browserLocale = $this->browserDetectedLanguage();
        $cookieLocale = $this->cookieDetectedLanguage();

        // URL로 지정된 언어가 기본 언어가 아닌 경우
        if (!$this->isDefaultLocale($pathLocale)) {
            $this->handler->store($pathLocale);
            $this->setLocale($pathLocale);
            $response = $next($request);
            $response->header('Content-Language', App::getLocale());
            return $response;
        }

        // 쿠키로 특정 언어가 강제 지정 되어 있으면 강제 리다이렉트
        if (
            $config['redirect_to_cookie_language_enabled']
            && $cookieLocale
            && $cookieLocale !== $pathLocale
        ) {
            return redirect()->to(Route::localizedUrl($cookieLocale));
        }

        // 브라우저에서 지정된 언어가 기본 언어페이지가 아닌 경우, 강제로 이동
        if (
            $config['redirect_to_accept_language_enabled']
            && $browserLocale
            && $browserLocale !== $pathLocale
        ) {
            return redirect()->to(Route::localizedUrl($browserLocale));
        }

        // 기본 값
        $this->handler->store($pathLocale);
        $this->setLocale($pathLocale);
        $response = $next($request);
        $response->header('Content-Language', App::getLocale());
        return $response;
    }

    protected function browserDetectedLanguage(): string
    {
        $browser = new BrowserDetector();
        $browserLocales = $browser->detect();
        return LocaleMatcher::findBestMatch($browserLocales);
    }

    protected function cookieDetectedLanguage(): ?string
    {
        $cookie = new CookieDetector();
        $cookieLocale = $cookie->detect();
        if ($cookieLocale === null)
            return null;

        return LocaleMatcher::findBestMatch([$cookieLocale]);
    }

    protected function isDefaultLocale(string $locale): bool
    {
        return $locale === LocaleConfig::getFallbackLocale();
    }

    /**
     * 로케일을 설정합니다.
     */
    protected function setLocale(string $locale): void
    {
        App::setLocale($locale);
        Carbon::setLocale($locale);
    }
}
