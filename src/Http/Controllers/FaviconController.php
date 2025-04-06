<?php

namespace OPGG\LaravelEssentialsEntry\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class FaviconController
{
    /**
     * favicon.ico 파일을 제공합니다.
     */
    public function index(): Response
    {
        $config = config('essentials-entry.favicon');

        if (!$config['enabled']) {
            abort(404);
        }

        // 사용자 지정 파비콘 확인
        if ($config['source_path'] !== null) {
            $path = $config['source_path'];
            if (File::exists($path)) {
                return $this->createResponse(File::get($path), $config);
            }
        }

        // 패키지 내부 기본 파비콘 사용
        $path = __DIR__ . '/../../assets/favicon-opgg.ico';
        if (!File::exists($path)) {
            abort(404);
        }

        return $this->createResponse(File::get($path), $config);
    }

    /**
     * 파비콘 응답을 생성합니다.
     */
    protected function createResponse(string $content, array $config): Response
    {
        $response = response($content)
            ->header('Content-Type', 'image/x-icon');

        if ($config['cache']['enabled']) {
            $response->header('Cache-Control', "public, max-age={$config['cache']['duration']}");
        }

        return $response;
    }
}
