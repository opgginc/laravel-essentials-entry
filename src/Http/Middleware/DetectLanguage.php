<?php

namespace OPGG\LaravelEssentialsEntry\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use CodeZero\LocalizedRoutes\Middleware\LocaleHandler;

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
        $locale = $this->handler->detect();

        if ($locale) {
            $this->handler->store($locale);

            $this->setLocale($locale);
        }

        $response = $next($request);
        $response->header('Content-Language', App::getLocale());
        return $response;
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
