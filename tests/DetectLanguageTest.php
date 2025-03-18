<?php

namespace OPGG\LaravelEssentialsEntry\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use Orchestra\Testbench\TestCase;

class DetectLanguageTest extends TestCase
{
    protected $middleware;

    protected function getPackageProviders($app)
    {
        return ['OPGG\LaravelEssentialsEntry\LaravelEssentialsEntryServiceProvider'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 설정
        Config::set('essentials-entry.language.enabled', true);
        Config::set('essentials-entry.language.default', 'en');
        Config::set('essentials-entry.language.supported', [
            'en' => 'English',
            'ko' => 'Korean',
            'ja' => 'Japanese',
        ]);
        Config::set('essentials-entry.language.locale_mapping', [
            'en' => 'en',
            'ko' => 'ko_KR',
            'ja' => 'ja_JP',
        ]);
        
        $this->middleware = $this->app->make(DetectLanguage::class);
    }

    public function testDetectFromBrowser()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7');
        
        $response = new Response();
        
        $next = function ($request) use ($response) {
            return $response;
        };
        
        $this->middleware->handle($request, $next);
        
        $this->assertEquals('ko_KR', App::getLocale());
        $this->assertEquals('ko_KR', $response->headers->get('Content-Language'));
    }

    public function testDetectFromPath()
    {
        $request = Request::create('/ja/page', 'GET');
        
        $response = new Response();
        
        $next = function ($request) use ($response) {
            return $response;
        };
        
        $this->middleware->handle($request, $next);
        
        $this->assertEquals('ja_JP', App::getLocale());
        $this->assertEquals('ja_JP', $response->headers->get('Content-Language'));
    }

    public function testFallbackToDefault()
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'fr-FR,fr;q=0.9');
        
        $response = new Response();
        
        $next = function ($request) use ($response) {
            return $response;
        };
        
        $this->middleware->handle($request, $next);
        
        $this->assertEquals('en', App::getLocale());
        $this->assertEquals('en', $response->headers->get('Content-Language'));
    }
}
