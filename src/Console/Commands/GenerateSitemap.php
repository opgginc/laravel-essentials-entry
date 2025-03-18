<?php

namespace OPGG\LaravelEssentialsEntry\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'essentials:generate-sitemap';
    protected $description = '사이트맵을 생성합니다.';

    public function handle()
    {
        $config = Config::get('essentials-entry.sitemap');

        if (!$config['enabled']) {
            $this->info('사이트맵 생성이 비활성화되어 있습니다.');
            return;
        }

        $this->info('사이트맵 생성을 시작합니다...');

        try {
            $sitemap = SitemapGenerator::create($config['domain'])
                ->hasCrawled(function (Url $url) use ($config) {
                    // 제외할 URL 패턴 체크
                    foreach ($config['exclude'] as $pattern) {
                        if (Str::is($pattern, $url->url)) {
                            return;
                        }
                    }

                    return $url->setChangeFrequency($config['defaults']['changefreq'])
                             ->setPriority($config['defaults']['priority']);
                })
                ->getSitemap();

            // 추가 경로 수동 추가
            foreach ($config['routes'] as $route) {
                $sitemap->add(
                    Url::create($route)
                        ->setChangeFrequency($config['defaults']['changefreq'])
                        ->setPriority($config['defaults']['priority'])
                );
            }

            // Cache에 저장
            Cache::put('essentials-entry.sitemap.xml', $sitemap->render(), Carbon::now()->addHours(24));

            $this->info('사이트맵이 성공적으로 생성되었습니다.');
        } catch (\Exception $e) {
            $this->error('사이트맵 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}
