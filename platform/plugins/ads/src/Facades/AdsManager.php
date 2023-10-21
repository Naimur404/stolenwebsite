<?php

namespace Botble\Ads\Facades;

use Botble\Ads\Supports\AdsManager as AdsManagerSupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string display(string $location, array $attributes = [])
 * @method static \Botble\Ads\Supports\AdsManager load(bool $force = false)
 * @method static bool locationHasAds(string $location)
 * @method static string|null displayAds(string|null $key, array $attributes = [], array $linkAttributes = [])
 * @method static \Illuminate\Support\Collection getData(bool $isLoad = false, bool $isNotExpired = false)
 * @method static \Botble\Ads\Supports\AdsManager registerLocation(string $key, string $name)
 * @method static array getLocations()
 * @method static \Botble\Ads\Models\Ads|null getAds(string $key)
 *
 * @see \Botble\Ads\Supports\AdsManager
 */
class AdsManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AdsManagerSupport::class;
    }
}
