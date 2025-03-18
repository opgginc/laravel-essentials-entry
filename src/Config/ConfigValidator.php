<?php

namespace OPGG\LaravelEssentialsEntry\Config;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class ConfigValidator
{
    /**
     * 설정 유효성 검사
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validate(): void
    {
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
        
        if (!isset($config['path'])) {
            throw new InvalidArgumentException('사이트맵 경로가 설정되지 않았습니다.');
        }
        
        if (!isset($config['domain'])) {
            throw new InvalidArgumentException('사이트맵 도메인이 설정되지 않았습니다.');
        }
        
        if (!isset($config['routes']) || !is_array($config['routes'])) {
            throw new InvalidArgumentException('사이트맵 경로 목록이 올바르지 않습니다.');
        }
        
        if (!isset($config['defaults']) || !is_array($config['defaults'])) {
            throw new InvalidArgumentException('사이트맵 기본 설정이 올바르지 않습니다.');
        }
        
        if (!isset($config['defaults']['changefreq'])) {
            throw new InvalidArgumentException('사이트맵 변경 빈도가 설정되지 않았습니다.');
        }
        
        if (!isset($config['defaults']['priority'])) {
            throw new InvalidArgumentException('사이트맵 우선순위가 설정되지 않았습니다.');
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
        
        if (!isset($config['default'])) {
            throw new InvalidArgumentException('기본 언어가 설정되지 않았습니다.');
        }
        
        if (!isset($config['supported']) || !is_array($config['supported'])) {
            throw new InvalidArgumentException('지원 언어 목록이 올바르지 않습니다.');
        }
        
        if (!isset($config['supported'][$config['default']])) {
            throw new InvalidArgumentException('기본 언어가 지원 언어 목록에 포함되어 있지 않습니다.');
        }
        
        if (!isset($config['detect_from']) || !is_array($config['detect_from'])) {
            throw new InvalidArgumentException('언어 감지 방식이 올바르지 않습니다.');
        }
    }
}
