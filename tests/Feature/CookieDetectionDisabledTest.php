<?php

namespace OPGG\LaravelEssentialsEntry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Tests\TestCase;

class CookieDetectionDisabledTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setSupportedLocales(['en', 'ko']);
        $this->setFallbackLocale('en');
        $this->setRedirectOptions(cookie: false);
    }

    public function testLanguageCookieDetect()
    {
        $this->setSupportedLocales(['en', 'ko', 'zh-cn', 'zh-tw']);
        $this->setLocaleMappings(['zh-hk' => 'zh-tw', 'zh-tw' => 'zh-hk']);
        $this->setFallbackLocale('en');

        Route::localized(function () {
            Route::get('languages', function () {
                return app()->getLocale();
            })->middleware(['web', DetectLanguage::class]);
        });

        $this->setAppLocale('en');
        $this->setAcceptLanguage('en');

        $this->setCookieLocale('en');
        $this->get('languages')->assertStatus(200)->assertContent('en');

        $this->setCookieLocale('ko_KR');
        $this->get('languages')->assertStatus(200)->assertContent('en');

        $this->setCookieLocale('zh_CN');
        $this->get('languages')->assertStatus(200)->assertContent('en');

        $this->setCookieLocale('zh_TW');
        $this->get('languages')->assertStatus(200)->assertContent('en');

        $this->setCookieLocale('zh_HK');
        $this->get('languages')->assertStatus(200)->assertContent('en');
    }
}
