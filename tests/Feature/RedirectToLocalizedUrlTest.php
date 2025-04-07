<?php

namespace OPGG\LaravelEssentialsEntry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Tests\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Route;

class RedirectToLocalizedUrlTest extends BaseTestCase
{
    /**
     * setUp 메소드를 오버라이드하여 테스트를 위한 환경을 구성합니다.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 예외 처리 비활성화
        $this->withoutExceptionHandling();
    }

    /**
     * 지역화된 URL로 리다이렉트되는지 테스트합니다.
     */
    public function testItRedirectsToLocalizedUrl(): void
    {
        // 지원 언어 설정
        $this->setSupportedLocales(['en', 'ko_KR', 'ja_JP', 'zh_CN', 'zh_TW']);
        $this->setFallbackLocale('en');

        // 테스트용 라우트 등록
        Route::localized(function () {
            Route::get('/', function () {
                return 'home';
            })->middleware(['web', DetectLanguage::class]);

            Route::get('about', function () {
                return 'about page';
            })->middleware(['web', DetectLanguage::class]);
        });

        // 영어 로케일로 테스트
        $this->setAppLocale('en');
        $this->get('/')->assertStatus(200);
        $this->get('about')->assertStatus(200);

        // 한국어 로케일로 테스트
        $this->setAppLocale('ko');
        $this->get('/')->assertRedirect('ko_KR');
        $this->get('ko_KR')->assertStatus(200);
        $this->get('about')->assertRedirect('ko_KR/about');
        $this->get('ko_KR/about')->assertStatus(200);

        // 한국어 로케일로 테스트
        $this->setAppLocale('ko_KR');
        $this->get('/')->assertRedirect('ko_KR');
        $this->get('ko_KR')->assertStatus(200);
        $this->get('about')->assertRedirect('ko_KR/about');
        $this->get('ko_KR/about')->assertStatus(200);

        // 일본어 로케일로 테스트
        $this->setAppLocale('ja_JP');
        $this->get('/')->assertRedirect('ja_JP');
        $this->get('ja_JP')->assertStatus(200);
        $this->get('about')->assertRedirect('ja_JP/about');
        $this->get('ja_JP/about')->assertStatus(200);

        // 중국어 별도로 지역 지정 안되면 무조건 간체
        $this->setAppLocale('zh');
        $this->get('/')->assertRedirect('zh_CN');
        $this->get('zh_CN')->assertStatus(200);
        $this->get('about')->assertRedirect('zh_CN/about');
        $this->get('zh_CN/about')->assertStatus(200);

        // 중국어 간체
        $this->setAppLocale('zh_CN');
        $this->get('/')->assertRedirect('zh_CN');
        $this->get('zh_CN')->assertStatus(200);
        $this->get('about')->assertRedirect('zh_CN/about');
        $this->get('zh_CN/about')->assertStatus(200);

        // 중국어 TW
        $this->setAppLocale('zh_TW');
        $this->get('/')->assertRedirect('zh_TW');
        $this->get('zh_TW')->assertStatus(200);
        $this->get('about')->assertRedirect('zh_TW/about');
        $this->get('zh_TW/about')->assertStatus(200);

        // 중국어 홍콩일 경우, HK 가 없으면 TW로 자동 적용
        $this->setAppLocale('zh_HK');
        $this->get('/')->assertRedirect('zh_TW');
        $this->get('zh_TW')->assertStatus(200);
        $this->get('about')->assertRedirect('zh_TW/about');
        $this->get('zh_TW/about')->assertStatus(200);
    }

    public function testItRedirectsToLocalizedUrlWithCustomSlugs(): void
    {
        // 지원 언어 설정
        $this->setSupportedLocales([
            'en' => 'en',
            'ko_KR' => 'korean'
        ]);
        $this->setFallbackLocale('en');

        // 테스트용 라우트 등록
        Route::localized(function () {
            Route::get('/', function () {
                return 'home';
            })->middleware(['web', DetectLanguage::class]);

            Route::get('about', function () {
                return 'about page';
            })->middleware(['web', DetectLanguage::class]);
        });

        // 영어 로케일로 테스트
        $this->setAppLocale('en');
        $this->get('/')->assertStatus(200);
        $this->get('about')->assertStatus(200);

        // 한국어 로케일로 테스트
        $this->setAppLocale('ko_KR');
        $this->get('/')->assertRedirect('korean');
        $this->get('about')->assertRedirect('korean/about');
    }
}
