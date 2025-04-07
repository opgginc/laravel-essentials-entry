<?php

namespace OPGG\LaravelEssentialsEntry\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getSupportedLanguages(bool $keysOnly = false, bool $sort = true)
 * @method static string getDefaultLanguage()
 * @method static string|null getLocaleFromCookie()
 * @method static array getLocalesFromBrowser()
 * 
 * @see \OPGG\LaravelEssentialsEntry\Support\EssentialsEntry
 */
class EssentialsEntry extends Facade
{
    /**
     * Facade 액세스키를 가져옵니다.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'essentials-entry';
    }
}
