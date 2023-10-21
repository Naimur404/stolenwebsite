<?php

namespace Botble\Location\Facades;

use Botble\Location\Location as BaseLocation;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array getStates()
 * @method static array getCitiesByState(string|int|null $stateId)
 * @method static \Botble\Location\Models\City|\Illuminate\Database\Eloquent\Model|null getCityById(string|int|null $cityId)
 * @method static string|null getCityNameById(string|int|null $cityId)
 * @method static string|null getStateNameById(string|int|null $stateId)
 * @method static bool isSupported(object|string $model)
 * @method static array supportedModels()
 * @method static array getSupported(object|string|null $model = null)
 * @method static bool registerModule(string $model, array $keys = [])
 * @method static array getRemoteAvailableLocations()
 * @method static array downloadRemoteLocation(string $countryCode)
 * @method static mixed filter($model, string|int|null $cityId = null, string|null $location = null)
 *
 * @see \Botble\Location\Location
 */
class Location extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseLocation::class;
    }
}
