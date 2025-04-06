<?php

namespace OPGG\LaravelEssentialsEntry\Config;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class ConfigValidator
{
    /**
     * 파비콘 설정 유효성 검사
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function validateFaviconConfig(): void
    {
        $config = Config::get('essentials-entry.favicon');

        if (!is_array($config)) {
            throw new InvalidArgumentException('파비콘 설정이 올바르지 않습니다.');
        }

        if (!isset($config['enabled'])) {
            throw new InvalidArgumentException('파비콘 활성화 여부가 설정되지 않았습니다.');
        }

        if (!isset($config['path_rewrite'])) {
            throw new InvalidArgumentException('파비콘 path_rewrite가 설정되지 않았습니다.');
        }

        if (isset($config['source_path']) && !is_string($config['source_path']) && $config['source_path'] !== null) {
            throw new InvalidArgumentException('파비콘 source_path가 올바르지 않습니다.');
        }

        if (!isset($config['cache']) || !is_array($config['cache'])) {
            throw new InvalidArgumentException('파비콘 캐시 설정이 올바르지 않습니다.');
        }

        if (!isset($config['cache']['enabled'])) {
            throw new InvalidArgumentException('파비콘 캐시 활성화 여부가 설정되지 않았습니다.');
        }

        if (!isset($config['cache']['duration'])) {
            throw new InvalidArgumentException('파비콘 캐시 유효 기간이 설정되지 않았습니다.');
        }
    }
    /**
     * 설정 유효성 검사
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validate(): void
    {
        static::validateFaviconConfig();
        static::validateSitemapConfig();
        static::validateMetaTagsConfig();
        static::validateLanguageConfig();
    }

    /**
     * 사이트맵 설정 유효성 검사
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function validateSitemapConfig(): void
    {
        $config = Config::get('essentials-entry.sitemap');

        if (!is_array($config)) {
            throw new InvalidArgumentException('사이트맵 설정이 올바르지 않습니다.');
        }

        if (!isset($config['enabled'])) {
            throw new InvalidArgumentException('사이트맵 활성화 여부가 설정되지 않았습니다.');
        }

        if (!isset($config['schedule'])) {
            throw new InvalidArgumentException('사이트맵 갱신 주기가 설정되지 않았습니다.');
        }

        if (!isset($config['path_rewrite'])) {
            throw new InvalidArgumentException('사이트맵 path_rewrite가 설정되지 않았습니다.');
        }

        if (!isset($config['cache_key'])) {
            throw new InvalidArgumentException('사이트맵 캐시 키가 설정되지 않았습니다.');
        }

        if (!isset($config['generator']) || !is_callable($config['generator'])) {
            throw new InvalidArgumentException('사이트맵 제너레이터 함수가 올바르지 않습니다.');
        }
    }

    /**
     * 메타 태그 설정 유효성 검사
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function validateMetaTagsConfig(): void
    {
        $config = Config::get('essentials-entry.meta-tags');

        if (!is_array($config)) {
            throw new InvalidArgumentException('메타 태그 설정이 올바르지 않습니다.');
        }

        if (!isset($config['defaults']) || !is_array($config['defaults'])) {
            throw new InvalidArgumentException('메타 태그 기본 설정이 올바르지 않습니다.');
        }

        if (!isset($config['defaults']['title'])) {
            throw new InvalidArgumentException('메타 태그 제목이 설정되지 않았습니다.');
        }

        if (!isset($config['og']) || !is_array($config['og'])) {
            throw new InvalidArgumentException('OpenGraph 설정이 올바르지 않습니다.');
        }

        if (!isset($config['og']['site_name'])) {
            throw new InvalidArgumentException('OpenGraph 사이트 이름이 설정되지 않았습니다.');
        }

        if (!isset($config['og']['type'])) {
            throw new InvalidArgumentException('OpenGraph 타입이 설정되지 않았습니다.');
        }
    }

    /**
     * 언어 설정 유효성 검사
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function validateLanguageConfig(): void
    {
        $config = Config::get('essentials-entry.language');

        if (!is_array($config)) {
            throw new InvalidArgumentException('언어 설정이 올바르지 않습니다.');
        }

        if (!isset($config['enabled'])) {
            throw new InvalidArgumentException('언어 설정 활성화 여부가 설정되지 않았습니다.');
        }

        if (!isset($config['default'])) {
            throw new InvalidArgumentException('기본 언어가 설정되지 않았습니다.');
        }

        if (!isset($config['supported']) || !is_array($config['supported'])) {
            throw new InvalidArgumentException('지원 언어 목록이 올바르지 않습니다.');
        }

        if (!in_array($config['default'], $config['supported'])) {
            throw new InvalidArgumentException('기본 언어가 지원 언어 목록에 포함되어 있지 않습니다.');
        }

        if (!isset($config['cookie']) || !is_array($config['cookie'])) {
            throw new InvalidArgumentException('언어 쿠키 설정이 올바르지 않습니다.');
        }
    }
}
