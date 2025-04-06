<?php

namespace OPGG\LaravelEssentialsEntry\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'essentials:generate-sitemap';
    protected $description = '다국어를 지원하는 사이트맵을 생성합니다.';

    public function handle()
    {
        $config = config('essentials-entry.sitemap');
        $languageConfig = config('essentials-entry.language');

        if (!$config['enabled']) {
            $this->info('사이트맵 생성이 비활성화되어 있습니다.');
            return;
        }

        $startTime = microtime(true);

        $this->components->info('사이트맵 생성을 시작합니다...', true);

        try {
            $domain = Config::get('app.url', 'http://localhost');
            $sitemap = SitemapGenerator::create($domain)->getSitemap();

            // URL 생성 도우미 함수
            $createUrl = function (string $url) {
                return Url::create($url);
            };

            // 사이트맵에 URL 추가 도우미 메소드
            $sitemapAdd = function (string $path) use ($sitemap, $domain, $createUrl) {
                $fullUrl = rtrim($domain, '/') . '/' . ltrim($path, '/');
                $sitemap->add($createUrl($fullUrl));
                return $sitemap;
            };

            // 다국어 URL 적용 함수 - Laravel-localized-routes 패키지 활용
            $applyLocales = function (callable $callback) use ($sitemap, $createUrl, $languageConfig, $domain) {
                // 지원 언어 설정 확인
                if (!isset($languageConfig['supported']) || empty($languageConfig['supported'])) {
                    $this->components->warn('[언어] language.supported 설정이 없어 다국어 처리를 건너뜁니다.');
                    return;
                }

                $supportedLocales = array_keys($languageConfig['supported']);
                $this->components->info('[언어] ' . count($supportedLocales) . '개 언어 처리 시작: ' . implode(', ', $supportedLocales));

                $processedUrls = 0;
                foreach ($supportedLocales as $locale) {
                    $this->line("언어 처리: {$locale} ({$languageConfig['supported'][$locale]})");
                    $callback($locale, function ($routeName, $parameters = []) use ($sitemap, $createUrl, $locale, &$processedUrls) {
                        // route 함수를 사용해 다국어 URL 생성 (codezero-be/laravel-localized-routes 패키지 방식)
                        try {
                            $url = route($routeName, $parameters, true, $locale);
                            $sitemap->add($createUrl($url));
                            $processedUrls++;
                        } catch (\Exception $e) {
                            // 라우트가 없는 경우 건너뜀
                        }
                        return $sitemap;
                    });
                }
                $this->newLine();
                $this->components->info("[언어] 총 {$processedUrls}개의 다국어 URL이 추가되었습니다.");
            };

            // 사용자 정의 제너레이터 적용
            if (isset($config['generator']) && is_callable($config['generator'])) {
                $sitemap = $config['generator']($sitemap, $applyLocales);
            }

            // 결과 요약 표시 (렌더링 전)
            $urls = collect($sitemap->getTags())->filter(function ($tag) {
                return $tag instanceof Url;
            });
            $uniqueUrlCount = $urls->count();
            $this->newLine();
            $this->components->info("[요약] 사이트맵에 추가된 고유 URL: {$uniqueUrlCount}개");

            // 렌더링 시작
            $this->line('사이트맵 XML 렌더링 중...');
            $sitemapContent = $sitemap->render();

            // 캐싱 시작
            $this->line('사이트맵 캐시 저장 중...');
            $cacheTime = Carbon::now()->addHours(24);
            Cache::put($config['cache_key'], $sitemapContent, $cacheTime);

            $this->newLine();
            $this->components->info('사이트맵이 성공적으로 생성되었습니다.', true);

            // 생성된 URL 목록 표시 (상세 표시)
            $printDetail = $uniqueUrlCount <= 20;

            if ($printDetail) {
                $this->newLine();
                $this->line('<fg=yellow;options=bold>생성된 URL 목록:</>');
                $urls->map(function (Url $url) {
                    return $url->url;
                })->sort()->each(function ($url) {
                    $shortUrl = Str::length($url) > 100 ? Str::substr($url, 0, 97) . '...' : $url;
                    $this->line(" <fg=green>•</>  {$shortUrl}");
                });
            } else {
                $this->line("<fg=yellow;options=bold>생성된 URL: <fg=green>{$uniqueUrlCount}개</> (20개 이상이라 목록 표시를 생략합니다)");
            }

            $this->newLine();
            $this->line("<fg=blue;options=bold>사이트맵 접근 URL:</> <fg=green>{$domain}{$config['path_rewrite']}</>");
            $this->line("<fg=blue;options=bold>캐시 키:</> <fg=green>{$config['cache_key']}</>");
            $this->line("<fg=blue;options=bold>사이트맵 크기:</> <fg=green>" . number_format(Str::length($sitemapContent) / 1024, 2) . " KB</>");
            $this->line("<fg=blue;options=bold>유효 기간:</> <fg=green>24시간</>");
            // 실행 시간 표시
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            $this->newLine();
            $this->line("<fg=magenta;options=bold>실행 시간:</> <fg=green>{$executionTime}초</>");
        } catch (\Exception $e) {
            $this->error('사이트맵 생성 중 오류가 발생했습니다:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->error('<fg=red;options=bold>예외 스택트레이스:</>');
            $this->error($e->getTraceAsString());
        }
    }
}
