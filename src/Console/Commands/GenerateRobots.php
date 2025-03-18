<?php

namespace OPGG\LaravelEssentialsEntry\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GenerateRobots extends Command
{
    protected $signature = 'essentials:generate-robots';
    protected $description = 'robots.txt 파일을 생성합니다.';

    public function handle()
    {
        $config = Config::get('essentials-entry.sitemap');

        if (!$config['enabled']) {
            $this->info('사이트맵 기능이 비활성화되어 있어 robots.txt 생성을 건너뜁니다.');
            return;
        }

        $this->info('robots.txt 파일 생성을 시작합니다...');

        try {
            $domain = $config['domain'];
            $sitemapUrl = $domain . '/sitemap.xml';
            
            // robots.txt 파일 내용 생성
            $content = $this->generateRobotsContent($sitemapUrl, $config['exclude']);
            
            // Cache에 저장
            Cache::put('essentials-entry.robots.txt', $content, Carbon::now()->addHours(24));

            $this->info('robots.txt 파일이 성공적으로 생성되었습니다.');
        } catch (\Exception $e) {
            $this->error('robots.txt 파일 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * robots.txt 파일 내용을 생성합니다.
     *
     * @param  string  $sitemapUrl
     * @param  array  $excludePaths
     * @return string
     */
    protected function generateRobotsContent(string $sitemapUrl, array $excludePaths): string
    {
        $content = "User-agent: *\n";
        
        // 제외할 경로 추가
        foreach ($excludePaths as $path) {
            // 와일드카드(*) 제거
            $path = str_replace('*', '', $path);
            $content .= "Disallow: {$path}\n";
        }
        
        // 사이트맵 URL 추가
        $content .= "\nSitemap: {$sitemapUrl}\n";
        
        return $content;
    }
}
