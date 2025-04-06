<?php

namespace OPGG\LaravelEssentialsEntry\Providers;

use Butschster\Head\Contracts\MetaTags\MetaInterface;
use Butschster\Head\Contracts\Packages\ManagerInterface;
use Butschster\Head\MetaTags\Meta;
use Butschster\Head\MetaTags\Entities\JavascriptVariables;
use Butschster\Head\Packages\Entities\OpenGraphPackage;
use Butschster\Head\Providers\MetaTagsApplicationServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
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
            $sitePath = "{$langPath}/site.php";
            
            if (!File::exists($sitePath)) {
                Log::warning(
                    "[메타 태그] {$defaultLocale}/site.php 파일이 존재하지 않습니다. " .
                    "SEO를 위해 다음과 같은 형식으로 파일을 생성해주세요:\n" .
                    "return [\n    'title' => '',\n    'description' => '',\n    'keywords' => '',\n];\n"
                );
            } else {
                Log::warning(
                    "[메타 태그] {$key} 번역 키가 {$defaultLocale}/site.php 파일에 존재하지 않습니다. " .
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

            // 번역 키 확인
            $this->checkTranslationKey('site.title');
            $this->checkTranslationKey('site.description');
            $this->checkTranslationKey('site.keywords');

            $meta->setTitle(__('site.title'));
            $meta->addTag('variables', new JavascriptVariables([
                'appTitle' => __('site.title'),
            ]));

            // 파비콘 설정
            $faviconConfig = Config::get('essentials-entry.favicon');
            if ($faviconConfig['enabled']) {
                $meta->setFavicon($faviconConfig['path_rewrite']);
            }

            $meta->setDescription(__('site.description'));
            $meta->setKeywords(__('site.keywords'));
            $meta->setRobots('index,follow');
            $meta->setViewport('width=device-width, initial-scale=1');
            $meta->setCharset('utf-8');

            $og = new OpenGraphPackage('');
            $og->setType('website')
                ->setSiteName(Config::get('app.name'))
                ->setLocale(App::getLocale());

            $supportedLocales = array_keys(Config::get('essentials-entry.language.supported', []));
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

            // Get current route information
            if (Route::isLocalized() || Route::isFallback()) {
                // Set canonical URL for current locale
                try {
                    $currentLocale = App::getLocale();
                    $canonicalUrl = Route::localizedUrl($currentLocale);
                    $meta->setCanonical($canonicalUrl);
                    $og->setUrl($canonicalUrl);
                } catch (\Exception $e) {
                    // Skip if URL generation fails
                }

                // Add x-default first
                try {
                    $url = Route::localizedUrl('en'); // 영어를 기본값으로 설정
                    $meta->setHrefLang('x-default', $url);
                } catch (\Exception $e) {
                    // Skip if URL generation fails
                }

                foreach ($supportedLocales as $locale) {
                    try {
                        $url = Route::localizedUrl($locale);
                        $meta->setHrefLang($locale, $url);
                    } catch (\Exception $e) {
                        // Skip if URL generation fails for this locale
                        continue;
                    }
                }
            }

            $meta->registerPackage($og);
            $meta->initialize();

            return $meta;
        });
    }
}
