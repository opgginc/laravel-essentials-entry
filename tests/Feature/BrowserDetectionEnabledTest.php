<?php

namespace OPGG\LaravelEssentialsEntry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Tests\TestCase;

class BrowserDetectionEnabledTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setSupportedLocales(['en', 'ko']);
        $this->setFallbackLocale('en');

        // 이 옵션이 활성화되면 browser 언어가 있는 경우에 redirect 시킴
        $this->setRedirectOptions(browser: true);
    }

    public function testLanguageBrowserDetect()
    {
        Route::localized(function () {
            Route::get('languages', function () {
                return app()->getLocale();
            })->middleware(['web', DetectLanguage::class]);
        });

        $this->setAcceptLanguage('en_UK');
        $this->get('languages')->assertStatus(200)->assertContent('en');

        $this->setAcceptLanguage('ko');
        $this->get('languages')->assertRedirect('ko/languages');
        $this->get('ko/languages')->assertStatus(200)->assertContent('ko');
    }

    public function testVariousTypeOfLocaleCode()
    {
        $this->setSupportedLocales(['en', 'ko', 'zh-cn', 'zh-tw']);

        Route::localized(function () {
            Route::get('/', function () {
                return app()->getLocale();
            })->middleware(['web', DetectLanguage::class]);
            Route::get('languages', function () {
                return app()->getLocale();
            })->middleware(['web', DetectLanguage::class]);
        });

        $testList = [
            'ko' => ['ko', 'ko_KR', 'ko-kr', 'ko-KR', 'ko-kp', 'ko_KP'],
            'zh-cn' => ['zh', 'zh-cn', 'zh_CN', 'zh-CN'],
            'zh-tw' => ['zh-tw', 'zh_TW', 'zh-hk', 'zh_HK'],
        ];

        foreach ($testList as $localePath => $locales) {
            foreach ($locales as $locale) {
                $this->setAcceptLanguage($locale);
                $this->get('/')->assertRedirect("{$localePath}");
                $this->get('languages')->assertRedirect("{$localePath}/languages");
            }
        }
    }
}
