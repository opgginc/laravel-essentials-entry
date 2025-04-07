<?php

namespace OPGG\LaravelEssentialsEntry\Support;

use CodeZero\BrowserLocale\BrowserLocale;
use CodeZero\LocalizedRoutes\Middleware\Detectors\BrowserDetector;
use CodeZero\LocalizedRoutes\Middleware\Detectors\CookieDetector;

class EssentialsEntry
{
    /**
     * 지원하는 언어 목록을 가져옵니다.
     *
     * @param bool $keysOnly 연관 배열일 경우 키만 가져올지 여부
     * @param bool $sort 결과를 정렬할지 여부
     * @return array 지원하는 언어 목록
     */
    public function getSupportedLanguages(bool $sort = true): array
    {
        $supported = config('essentials-entry.language.supported', []);

        // 연관 배열일 경우 키만 선택적으로 반환
        $result = $supported;
        if (self::isAssociativeArray($supported)) {
            $result = array_keys($supported);
        }

        // 정렬 여부
        if ($sort) {
            if (self::isAssociativeArray($result)) {
                ksort($result);
            } else {
                sort($result);
            }
        }

        return $result;
    }

    /**
     * 기본 언어를 가져옵니다.
     *
     * @return string 기본 언어
     */
    public function getDefaultLanguage(): string
    {
        return config('essentials-entry.language.default', 'en');
    }

    /**
     * 쿠키에서 감지한 언어를 가져옵니다.
     *
     * @return string|null 쿠키에서 감지한 언어 또는 없으면 null
     */
    public function getLocaleFromCookie(): ?string
    {
        $cookie = new CookieDetector();
        return LocaleMatcher::findBestMatch([$cookie->detect()]);
    }

    /**
     * 브라우저에서 감지한 언어 목록을 가져옵니다.
     *
     * @return string|null 브라우저에서 감지한 언어 또는 없으면 null
     */
    public function getLocalesFromBrowser(): ?string
    {
        $browser = new BrowserDetector();
        return LocaleMatcher::findBestMatch($browser->detect());
    }

    /**
     * 배열이 연관 배열인지 확인합니다。
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
