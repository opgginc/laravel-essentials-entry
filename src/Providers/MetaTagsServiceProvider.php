<?php

namespace OPGG\LaravelEssentialsEntry\Providers;

use Butschster\Head\Contracts\MetaTags\MetaInterface;
use Butschster\Head\Contracts\Packages\ManagerInterface;
use Butschster\Head\MetaTags\Meta;
use Butschster\Head\Packages\Entities\OpenGraphPackage;
use Butschster\Head\Providers\MetaTagsApplicationServiceProvider as ServiceProvider;
use Butschster\Head\MetaTags\Entities\Tag;
use Butschster\Head\MetaTags\Entities\Link;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MetaTagsServiceProvider extends ServiceProvider
{
    protected function packages()
    {
        // Create your own packages here
    }

    protected function registerMeta(): void
    {
        $this->app->singleton(MetaInterface::class, function () {
            $meta = new Meta(
                $this->app[ManagerInterface::class],
                $this->app['config']
            );

            $config = Config::get('essentials-entry.meta-tags');

            // 기본 메타 태그 설정
            $meta->setTitle($config['defaults']['title'])
                ->setDescription($config['defaults']['description'])
                ->setKeywords($config['defaults']['keywords'])
                ->setRobots($config['defaults']['robots'])
                ->setViewport($config['defaults']['viewport'])
                ->setCharset($config['defaults']['charset']);

            // OpenGraph 설정
            $og = new OpenGraphPackage('og');
            $og->setType($config['og']['type'])
                ->setSiteName($config['og']['site_name'])
                ->addImage($config['og']['image']);

            // 지원하는 언어 설정
            $locales = Config::get('essentials-entry.language.supported', []);
            foreach ($locales as $locale => $name) {
                $og->addAlternateLocale($locale);
            }

            $meta->registerPackage($og);

            // 이미지 설정
            if (File::exists(Storage::path('public' . $config['images']['favicon']))) {
                $meta->setFavicon($config['images']['favicon']);
            }

            if (File::exists(Storage::path('public' . $config['images']['touch_icon']))) {
                $meta->addLink('apple-touch-icon', [
                    'href' => $config['images']['touch_icon']
                ]);
            }

            $meta->initialize();

            return $meta;
        });
    }
}
