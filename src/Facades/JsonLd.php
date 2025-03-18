<?php

namespace OPGG\LaravelEssentialsEntry\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd product()
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd organization()
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd website()
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd article()
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd breadcrumbList()
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd faqPage()
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd set(string $key, $value)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd name(string $name)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd description(string $description)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd image(string $image)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd url(string $url)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd price(float $price)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd priceCurrency(string $currency)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd itemListElement(array $items)
 * @method static \OPGG\LaravelEssentialsEntry\Schema\JsonLd addQuestion(string $question, string $answer)
 * @method static string toScript()
 * @method static string toJson()
 * @method static array toArray()
 * 
 * @see \OPGG\LaravelEssentialsEntry\Schema\JsonLd
 */
class JsonLd extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'essentials-entry.json-ld';
    }
}
