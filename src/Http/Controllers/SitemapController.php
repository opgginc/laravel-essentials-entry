<?php

namespace OPGG\LaravelEssentialsEntry\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController
{
    /**
     * 사이트맵.xml 파일을 서빙합니다.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config = config('essentials-entry.sitemap');
        
        if (!$config['enabled']) {
            abort(404, '사이트맵이 비활성화되어 있습니다.');
        }
        
        $content = Cache::get($config['cache_key']);
        
        if (!$content) {
            abort(404, '사이트맵이 아직 생성되지 않았습니다. 먼저 사이트맵을 생성해주세요.');
        }
        
        return new Response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }
}
