<?php

namespace OPGG\LaravelEssentialsEntry\Tests;

use Butschster\Head\Contracts\MetaTags\MetaInterface;
use Illuminate\Support\Facades\Config;
use OPGG\LaravelEssentialsEntry\Providers\MetaTagsServiceProvider;
use Orchestra\Testbench\TestCase;

class MetaTagsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'OPGG\LaravelEssentialsEntry\LaravelEssentialsEntryServiceProvider',
            'Butschster\Head\Providers\MetaTagsServiceProvider',
            'OPGG\LaravelEssentialsEntry\Providers\MetaTagsServiceProvider',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 설정
        Config::set('essentials-entry.meta-tags.defaults.title', 'Test Title');
        Config::set('essentials-entry.meta-tags.defaults.description', 'Test Description');
        Config::set('essentials-entry.meta-tags.og.site_name', 'Test Site');
        Config::set('essentials-entry.meta-tags.og.type', 'website');
        Config::set('essentials-entry.meta-tags.og.image', '/test-image.jpg');
        
        Config::set('essentials-entry.language.supported', [
            'en' => 'English',
            'ko' => 'Korean',
        ]);
    }

    public function testMetaTagsRegistration()
    {
        $meta = $this->app->make(MetaInterface::class);
        
        $this->assertInstanceOf(MetaInterface::class, $meta);
        $this->assertEquals('Test Title', $meta->getTitle());
        $this->assertEquals('Test Description', $meta->getDescription());
    }

    public function testOpenGraphTags()
    {
        $meta = $this->app->make(MetaInterface::class);
        $html = $meta->toHtml();
        
        $this->assertStringContainsString('<meta property="og:site_name" content="Test Site">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="website">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="/test-image.jpg">', $html);
    }

    public function testAlternateLocales()
    {
        $meta = $this->app->make(MetaInterface::class);
        $html = $meta->toHtml();
        
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="en">', $html);
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="ko">', $html);
    }
}
