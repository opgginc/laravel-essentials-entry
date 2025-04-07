<?php

namespace OPGG\LaravelEssentialsEntry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Tests\TestCase;

class LanguageDetectionTest extends TestCase
{
    protected $cookieName;

    /**
     * 지원하는 언어 설정
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 디폴트 설정
        $this->setSupportedLocales(['en', 'ko_KR']);
        $this->setFallbackLocale('en');
    }

    public function testLanguageCookieDetect()
    {
        // 테스트에 필요한 지원 언어 설정
        $this->setSupportedLocales(['en', 'ko_KR', 'zh_CN']);
        $this->setFallbackLocale('en');

        // 테스트용 라우트 등록
        Route::localized(function () {
            Route::get('languages', function () {
                return EssentialsEntry::getLocaleFromCookie();
            })->middleware(['web', DetectLanguage::class]);
        });

        $this->setAppLocale('en');

        // 쿠키로 컨트롤
        $this->setCookieLocale('ko_KR');
        $this->get('languages')->assertRedirect('ko_KR/languages');
        $this->get('ko_KR/languages')->assertStatus(200)->assertContent('ko_KR');

        $this->setCookieLocale('zh_CN');
        $this->get('languages')->assertRedirect('zh_CN/languages');
        $this->get('zh_CN/languages')->assertStatus(200)->assertContent('zh_CN');

        $this->setCookieLocale('zh_TW');
        $this->get('languages')->assertRedirect('zh_CN/languages');
        $this->get('zh_CN/languages')->assertStatus(200)->assertContent('zh_CN');

        $this->setCookieLocale('zh_HK');
        $this->get('languages')->assertRedirect('zh_CN/languages');
        $this->get('zh_CN/languages')->assertStatus(200)->assertContent('zh_CN');
    }

    public function testLanguageCookieDetectWithSpecialMapping(): void
    {
        // 테스트에 필요한 지원 언어 설정
        $this->setSupportedLocales(['en', 'ko_KR', 'zh_CN', 'zh_TW']);
        $this->setFallbackLocale('en');

        // 테스트용 라우트 등록
        Route::localized(function () {
            Route::get('languages', function () {
                return EssentialsEntry::getLocaleFromCookie();
            })->middleware(['web', DetectLanguage::class]);
        });

        $this->setAppLocale('en');

        // 쿠키로 컨트롤
        $this->setCookieLocale('zh_HK');
        $this->get('languages')->assertRedirect('zh_TW/languages');
        $this->get('zh_TW/languages')->assertStatus(200)->assertContent('zh_TW');
    }

    public function testLanguageBrowserDetect()
    {
        // 테스트용 라우트 등록
        Route::localized(function () {
            Route::get('languages', function () {
                return EssentialsEntry::getLocalesFromBrowser();
            })->middleware(['web', DetectLanguage::class]);
        });

        // 영어 로케일로 테스트
        $this->setAppLocale('en');
        $this->get('languages')->assertStatus(200)->assertContent('en');

        // 한국어 로케일로 테스트
        $this->setAppLocale('ko_KR');
        $this->get('languages')->assertRedirect('ko_KR/languages');
        $this->get('ko_KR/languages')->assertStatus(200)->assertContent('ko_KR');
    }
}
