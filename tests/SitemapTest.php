<?php

namespace OPGG\LaravelEssentialsEntry\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use OPGG\LaravelEssentialsEntry\Console\Commands\GenerateSitemap;
use Orchestra\Testbench\TestCase;

class SitemapTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['OPGG\LaravelEssentialsEntry\LaravelEssentialsEntryServiceProvider'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 설정
        Config::set('essentials-entry.sitemap.enabled', true);
        Config::set('essentials-entry.sitemap.path', Storage::path('public/sitemap.xml'));
        Config::set('essentials-entry.sitemap.domain', 'http://localhost');
    }

    public function testGenerateSitemapCommand()
    {
        $this->artisan('essentials:generate-sitemap')
             ->expectsOutput('사이트맵 생성을 시작합니다...')
             ->assertExitCode(0);
    }

    public function testSitemapIsDisabled()
    {
        Config::set('essentials-entry.sitemap.enabled', false);
        
        $this->artisan('essentials:generate-sitemap')
             ->expectsOutput('사이트맵 생성이 비활성화되어 있습니다.')
             ->assertExitCode(0);
    }
}
