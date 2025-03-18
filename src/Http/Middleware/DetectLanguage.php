<?php

namespace OPGG\LaravelEssentialsEntry\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class DetectLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Config::get('essentials-entry.language.enabled')) {
            return $next($request);
        }

        $locale = $this->detectLocale($request);
        $this->setLocale($locale);

        $response = Response::make($next($request));
        $response->headers->set('Content-Language', App::getLocale());

        return $response;
    }

    /**
     * 요청으로부터 로케일을 감지합니다.
     */
    protected function detectLocale(Request $request): string
    {
        $config = Config::get('essentials-entry.language');
        $detectionMethods = $config['detect_from'];

        foreach ($detectionMethods as $method) {
            $locale = match ($method) {
                'cookie' => $this->detectFromCookie($request),
                'browser' => $this->detectFromBrowser($request),
                'subdomain' => $this->detectFromSubdomain($request),
                'path' => $this->detectFromPath($request),
                default => null,
            };

            if ($locale && $this->isValidLocale($locale)) {
                return $locale;
            }
        }

        return $config['default'];
    }

    /**
     * 쿠키에서 로케일을 감지합니다.
     */
    protected function detectFromCookie(Request $request): ?string
    {
        $cookieName = Config::get('essentials-entry.language.cookie.name');
        return $request->cookie($cookieName);
    }

    /**
     * 브라우저 설정에서 로케일을 감지합니다.
     */
    protected function detectFromBrowser(Request $request): ?string
    {
        $locale = $request->getPreferredLanguage();
        return substr($locale, 0, 2);
    }

    /**
     * 서브도메인에서 로케일을 감지합니다.
     */
    protected function detectFromSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        return count($parts) > 2 ? $parts[0] : null;
    }

    /**
     * URL 경로에서 로케일을 감지합니다.
     */
    protected function detectFromPath(Request $request): ?string
    {
        $segments = $request->segments();
        return $segments[0] ?? null;
    }

    /**
     * 로케일이 유효한지 확인합니다.
     */
    protected function isValidLocale(string $locale): bool
    {
        $supported = Config::get('essentials-entry.language.supported');
        return isset($supported[$locale]);
    }

    /**
     * 로케일을 설정합니다.
     */
    protected function setLocale(string $locale): void
    {
        $config = Config::get('essentials-entry.language');
        $standardLocale = $config['locale_mapping'][$locale] ?? $locale;
        
        App::setLocale($standardLocale);
        
        if (isset($config['carbon_mapping'][$standardLocale])) {
            Carbon::setLocale($config['carbon_mapping'][$standardLocale]);
        } else {
            Carbon::setLocale($config['default']);
        }
    }
}
