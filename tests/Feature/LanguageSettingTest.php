<?php

namespace OPGG\LaravelEssentialsEntry\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;
use OPGG\LaravelEssentialsEntry\Tests\TestCase;

class LanguageSettingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setSupportedLocales(['en', 'ko', 'zh-cn', 'zh-tw']);
        $this->setFallbackLocale('en');
    }

    public function testLanguageSetting()
    {
        Route::localized(function () {
            Route::get('languages', function () {
                return app()->getLocale();
            })->middleware(['web', DetectLanguage::class]);
        });

        $this->get('languages')->assertStatus(200)->assertContent('en');
        $this->get('ko/languages')->assertStatus(200)->assertContent('ko');
        $this->get('zh-cn/languages')->assertStatus(200)->assertContent('zh-cn');
        $this->get('zh-tw/languages')->assertStatus(200)->assertContent('zh-tw');
    }
}
