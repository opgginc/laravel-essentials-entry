<?php

namespace OPGG\LaravelEssentialsEntry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Tests\TestCase;

class BrowserDetectionDefaultTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setSupportedLocales(['en', 'ko']);
        $this->setFallbackLocale('en');
    }

    public function testLanguageBrowserDetect()
    {
        Route::localized(function () {
            Route::get('languages', function () {
                return app()->getLocale();
            })->middleware(['web', DetectLanguage::class]);
        });

        // 2025년 4월 변경으로, 브라우저 언어가 있다고 하더라도 리다이렉트가 되면 안됨
        $this->setAcceptLanguage('en_UK');
        $this->get('languages')->assertStatus(200)->assertContent('en');

        $this->setAcceptLanguage('ko');
        $this->get('languages')->assertStatus(200)->assertContent('en');
    }
}
