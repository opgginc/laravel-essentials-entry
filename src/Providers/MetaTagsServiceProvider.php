<?php

namespace OPGG\LaravelEssentialsEntry\Providers;

use Butschster\Head\Contracts\MetaTags\MetaInterface;
use Butschster\Head\Contracts\Packages\ManagerInterface;
use Butschster\Head\MetaTags\Meta;
use Butschster\Head\MetaTags\Entities\JavascriptVariables;
use Butschster\Head\Packages\Entities\OpenGraphPackage;
use Butschster\Head\Packages\Entities\TwitterCardPackage;
use Butschster\Head\Providers\MetaTagsApplicationServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry as EssentialsEntryFacade;
use OPGG\LaravelEssentialsEntry\Http\Middleware\DetectLanguage;

class MetaTagsServiceProvider extends ServiceProvider
{
    protected function packages()
    {
        // 패키지 정의가 필요한 경우 여기에 추가
    }

    /**
     * 번역 키가 존재하는지 확인하고 없다면 경고 메시지 출력
     */
    protected function checkTranslationKey(string $key): void
    {
        $defaultLocale = Config::get('essentials-entry.language.default', 'en');
        if (!Lang::has($key, $defaultLocale)) {
            $langPath = lang_path($defaultLocale);
            $seoPath = "{$langPath}/seo.php";

            if (!File::exists($seoPath)) {
                Log::warning(
                    "[메타 태그] {$defaultLocale}/seo.php 파일이 존재하지 않습니다. " .
                    "SEO를 위해 다음과 같은 형식으로 파일을 생성해주세요:\n" .
                    "return [\n    'title' => '',\n    'description' => '',\n    'keywords' => '',\n];\n"
                );
            } else {
                Log::warning(
                    "[메타 태그] {$key} 번역 키가 {$defaultLocale}/seo.php 파일에 존재하지 않습니다. " .
                    "SEO를 위해 해당 키를 추가해주세요."
                );
            }
        }
    }

    protected function registerMeta(): void
    {
        $this->app->singleton(MetaInterface::class, function () {
            $meta = new Meta(
                $this->app[ManagerInterface::class],
                $this->app['config']
            );

            // 설정 값 가져오기
            $metaConfig = Config::get('essentials-entry.meta-tags', []);
            $ogConfig = $metaConfig['og'] ?? [];
            $imagesConfig = $metaConfig['images'] ?? [];

            // 번역 키 확인
            $this->checkTranslationKey('seo.title');
            $this->checkTranslationKey('seo.description');
            $this->checkTranslationKey('seo.keywords');

            // 기본 태그 설정
            $meta->setTitle(__('seo.title'));
            $meta->addTag('variables', new JavascriptVariables([
                'appTitle' => __('seo.title'),
            ]));

            // 파비콘 설정
            $faviconConfig = Config::get('essentials-entry.favicon');
            if ($faviconConfig['enabled']) {
                $meta->setFavicon($faviconConfig['path_rewrite']);
            }

            // 애플 터치 아이콘 설정
            if (isset($imagesConfig['touch_icon']) && !empty($imagesConfig['touch_icon'])) {
                $meta->addLink('apple-touch-icon', [
                    'rel' => 'apple-touch-icon',
                    'href' => $imagesConfig['touch_icon'],
                ]);
            }

            // 기본 메타 태그 설정
            $meta->setDescription(__('seo.description'));
            $meta->setKeywords(__('seo.keywords'));

            // 오픈 그래프 패키지 설정
            $og = new OpenGraphPackage('og-package');
            $og->setType($ogConfig['type'] ?? 'website')
                ->setSiteName(__('seo.title'))
                ->setLocale(App::getLocale());

            if (isset($ogConfig['image']) && !empty($ogConfig['image'])) {
                $og->addImage((string) url($ogConfig['image']));
            }

            // 다국어 설정
            $supportedLocales = EssentialsEntryFacade::getSupportedLanguages();
            foreach ($supportedLocales as $locale) {
                $og->addAlternateLocale($locale);
            }

            // 사이트맵 설정
            $sitemapConfig = Config::get('essentials-entry.sitemap');
            if ($sitemapConfig['enabled']) {
                $meta->addLink('sitemap', [
                    'type' => 'application/xml',
                    'title' => 'Sitemap',
                    'href' => url($sitemapConfig['path_rewrite']),
                ]);
            }

            // 현재 라우트 정보 가져오기
            if (Route::isLocalized() || Route::isFallback()) {
                // 현재 로케일에 대한 표준 URL 설정
                try {
                    $currentLocale = App::getLocale();
                    $canonicalUrl = Route::localizedUrl($currentLocale);
                    $meta->setCanonical($canonicalUrl);
                    $og->setUrl($canonicalUrl);
                } catch (\Exception $e) {
                    // URL 생성에 실패하면 스킵
                }

                // x-default를 먼저 추가
                try {
                    $defaultLocale = Config::get('essentials-entry.language.default', 'en');
                    $url = Route::localizedUrl($defaultLocale);
                    $meta->setHrefLang('x-default', $url);
                } catch (\Exception $e) {
                    // URL 생성에 실패하면 스킵
                }

                // 각 지원 로케일에 대한 대체 URL 추가
                foreach ($supportedLocales as $locale) {
                    try {
                        $url = Route::localizedUrl($locale);
                        $meta->setHrefLang($locale, $url);
                    } catch (\Exception $e) {
                        // 이 로케일에 대한 URL 생성에 실패하면 스킵
                        continue;
                    }
                }
            }

            // 전역 JavaScript 변수
            $jsVariables = [
                'appTitle' => __('seo.title'),
                'locale' => App::getLocale(),
                'baseUrl' => url('/')
            ];

            // JavaScript 변수 추가
            $meta->addTag('variables', new JavascriptVariables($jsVariables));

            // OG 패키지 등록
            $meta->registerPackage($og);

            // 초기화 (기본값 가져오기 및 태그 생성, 기본 패키지 포함)
            $meta->initialize();

            return $meta;
        });
    }
}
