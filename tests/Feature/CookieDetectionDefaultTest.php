<?php

namespace OPGG\LaravelEssentialsEntry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Tests\TestCase;

class CookieDetectionDefaultTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setSupportedLocales(['en', 'ko']);
        $this->setFallbackLocale('en');

        // 디폴트는 강제 이동하는거임
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
        $this->get('languages')->assertRedirect('ko/languages');
        // 무한루프 돌면 안되니까 한번만 체크
        $this->get('ko/languages')->assertStatus(200)->assertContent('ko');

        $this->setCookieLocale('zh_CN');
        $this->get('languages')->assertRedirect('zh-cn/languages');

        $this->setCookieLocale('zh_TW');
        $this->get('languages')->assertRedirect('zh-tw/languages');

        $this->setCookieLocale('zh_HK');
        $this->get('languages')->assertRedirect('zh-tw/languages');
    }
}
