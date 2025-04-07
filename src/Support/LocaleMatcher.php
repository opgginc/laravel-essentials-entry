<?php

namespace OPGG\LaravelEssentialsEntry\Support;

use CodeZero\LocalizedRoutes\Facades\LocaleConfig;
use OPGG\LaravelEssentialsEntry\Facades\EssentialsEntry;

class LocaleMatcher
{
    /**
     * 브라우저 언어 배열에서 지원하는 로케일 중 가장 적합한 것을 찾습니다.
     *
     * @param array $browserLocales 브라우저에서 감지된 언어 배열 (우선순위 순)
     * @param array|null $supportedLocales 지원하는 로케일 배열 (null일 경우 설정에서 가져옴)
     * @param string|null $defaultLocale 기본 로케일 (null일 경우 설정에서 가져옴)
     * @return string 선택된 로케일
     */
    public static function findBestMatch(array $browserLocales, ?array $supportedLocales = null, ?string $defaultLocale = null): string
    {
        // 지원하는 로케일이 지정되지 않은 경우 설정에서 가져옴
        $supportedLocales = $supportedLocales ?? LocaleConfig::getSupportedLocales();

        // 기본 로케일이 지정되지 않은 경우 설정에서 가져옴
        $defaultLocale = $defaultLocale ?? LocaleConfig::getFallbackLocale();

        // 매칭 결과가 없을 경우 기본 로케일로 돌아감
        if (empty($browserLocales) || empty($supportedLocales)) {
            return $defaultLocale;
        }

        // 연관 배열인지 확인하고 키 목록 준비
        $isAssociative = self::isAssociativeArray($supportedLocales);
        $localeKeys = $isAssociative ? array_keys($supportedLocales) : $supportedLocales;

        // 기본 언어 코드 매핑 (대표 언어 -> 특정 지역 로케일)
        $languageMapping = self::buildLanguageMapping($localeKeys);

        // 특수 언어 매핑 (서로 보완 관계인 언어들)
        $specialMappings = self::getSpecialLanguageMappings();

        // 브라우저 언어를 순회하며 최적의 매칭 찾기
        foreach ($browserLocales as $locale) {
            // 1. 정확히 일치하는 경우
            if (in_array($locale, $localeKeys)) {
                return $locale;
            }

            // 2. 특수 매핑이 있는 경우 (예: zh_HK -> zh_TW)
            if (isset($specialMappings[$locale])) {
                $mappedLocale = $specialMappings[$locale];
                if (in_array($mappedLocale, $localeKeys)) {
                    return $mappedLocale;
                }
            }

            // 3. 기본 언어 코드만 일치하는 경우 (예: 'ko' -> 'ko_KR')
            $baseLanguage = self::getBaseLanguage($locale);
            if (isset($languageMapping[$baseLanguage])) {
                return $languageMapping[$baseLanguage];
            }
        }

        // 4. 일치하는 것이 없으면 기본 로케일 반환
        return $defaultLocale;
    }

    /**
     * 로케일에서 기본 언어 코드를 추출합니다.
     * (예: 'en-US' -> 'en', 'ko_KR' -> 'ko')
     *
     * @param string $locale 로케일 문자열
     * @return string 기본 언어 코드
     */
    public static function getBaseLanguage(string $locale): string
    {
        $parts = preg_split('/[-_]/', $locale);
        return strtolower($parts[0]);
    }

    /**
     * 지원하는 로케일에서 기본 언어 코드 매핑을 생성합니다.
     * (예: 'ko' => 'ko_KR', 'zh' => 'zh_CN')
     *
     * @param array $supportedLocales 지원하는 로케일 배열
     * @return array 기본 언어 코드 => 로케일 매핑
     */
    public static function buildLanguageMapping(array $supportedLocales): array
    {
        $mapping = [];

        // 순서대로 처리하여 먼저 오는 것이 기본 형태가 됨 (ex: 'zh_CN')
        // 후에 나오는 것은 무시하고 먼저 나온 것을 사용 (ex: 'zh_TW', 'zh_HK'를 무시하고 'zh' -> 'zh_CN' 매핑)
        foreach ($supportedLocales as $locale) {
            $baseLanguage = self::getBaseLanguage($locale);

            // 처음 나오는 매핑만 적용 (우선순위 가장 높은 것)
            if (!isset($mapping[$baseLanguage])) {
                $mapping[$baseLanguage] = $locale;
            }
        }

        return $mapping;
    }

    /**
     * 특수 언어 매핑을 정의합니다. (서로 보완 관계인 언어들)
     * 여기서는 zh_HK와 zh_TW가 모두 번체 중국어이므로 상호 대체 가능하게 합니다.
     *
     * @return array 특수 언어 매핑
     */
    public static function getSpecialLanguageMappings(): array
    {
        // 환경 설정에서 매핑 정보 가져오기
        return config('essentials-entry.language.locale_mappings', []);
    }

    /**
     * 배열이 연관 배열(키-값 쌍)인지 확인합니다.
     *
     * @param array $array 확인할 배열
     * @return bool 연관 배열이면 true, 일반 배열이면 false
     */
    private static function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
