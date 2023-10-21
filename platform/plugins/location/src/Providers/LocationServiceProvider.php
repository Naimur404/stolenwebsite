<?php

namespace Botble\Location\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\MacroableModels;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Location\Facades\Location;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Botble\Location\Repositories\Eloquent\CityRepository;
use Botble\Location\Repositories\Eloquent\CountryRepository;
use Botble\Location\Repositories\Eloquent\StateRepository;
use Botble\Location\Repositories\Interfaces\CityInterface;
use Botble\Location\Repositories\Interfaces\CountryInterface;
use Botble\Location\Repositories\Interfaces\StateInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class LocationServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(CountryInterface::class, function () {
            return new CountryRepository(new Country());
        });

        $this->app->bind(StateInterface::class, function () {
            return new StateRepository(new State());
        });

        $this->app->bind(CityInterface::class, function () {
            return new CityRepository(new City());
        });

        AliasLoader::getInstance()->alias('Location', Location::class);
    }

    public function boot(): void
    {
        $this->setNamespace('plugins/location')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadAndPublishViews()
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->publishAssets();

        if (defined('LANGUAGE_MODULE_SCREEN_NAME') && defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            LanguageAdvancedManager::registerModule(Country::class, [
                'name',
                'nationality',
            ]);

            LanguageAdvancedManager::registerModule(State::class, [
                'name',
            ]);

            LanguageAdvancedManager::registerModule(City::class, [
                'name',
            ]);
        }

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-location',
                    'priority' => 900,
                    'parent_id' => null,
                    'name' => 'plugins/location::location.name',
                    'icon' => 'fas fa-globe',
                    'url' => null,
                    'permissions' => ['country.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-country',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-location',
                    'name' => 'plugins/location::country.name',
                    'icon' => 'fas fa-globe',
                    'url' => route('country.index'),
                    'permissions' => ['country.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-state',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-location',
                    'name' => 'plugins/location::state.name',
                    'icon' => 'fas fa-globe',
                    'url' => route('state.index'),
                    'permissions' => ['state.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-city',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-location',
                    'name' => 'plugins/location::city.name',
                    'icon' => 'fas fa-globe',
                    'url' => route('city.index'),
                    'permissions' => ['city.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-location-bulk-import',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-location',
                    'name' => 'plugins/location::bulk-import.menu',
                    'icon' => 'fas fa-cloud-upload-alt',
                    'url' => route('location.bulk-import.index'),
                    'permissions' => ['location.bulk-import.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-location-export',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-location',
                    'name' => 'plugins/location::location.export_location',
                    'icon' => 'fas fa-cloud-download-alt',
                    'url' => route('location.export.index'),
                    'permissions' => ['location.export.index'],
                ]);
        });

        $this->app->booted(function () {
            Blueprint::macro('location', function ($item = null, $keys = []) {
                if ($item) {
                    if (class_exists($item) && Location::isSupported($item)) {
                        $data = Location::getSupported($item);
                        $model = new $item();
                        $table = $model->getTable();
                        $connection = $model->getConnectionName();
                        $keys = [];
                        foreach ($data as $key => $column) {
                            if (! Schema::connection($connection)->hasColumn($table, $column)) {
                                $keys[$key] = $column;
                            }
                        }
                    }
                } else {
                    $keys = array_filter(
                        array_merge([
                            'country' => 'country_id',
                            'state' => 'state_id',
                            'city' => 'city_id',
                        ], $keys)
                    );
                }

                /**
                 * @var Blueprint $this
                 */
                if ($columnName = Arr::get($keys, 'country')) {
                    $this->foreignId($columnName)->default(1)->nullable();
                }

                if ($columnName = Arr::get($keys, 'state')) {
                    $this->foreignId($columnName)->nullable();
                }

                if ($columnName = Arr::get($keys, 'city')) {
                    $this->foreignId($columnName)->nullable();
                }

                return true;
            });

            foreach (Location::getSupported() as $item => $keys) {
                if (! class_exists($item)) {
                    continue;
                }

                if ($foreignKey = Arr::get($keys, 'country')) {
                    /**
                     * @var BaseModel $item
                     */
                    $item::resolveRelationUsing('country', function ($model) use ($foreignKey) {
                        return $model->belongsTo(Country::class, $foreignKey)->withDefault();
                    });

                    MacroableModels::addMacro($item, 'getCountryNameAttribute', function () {
                        /**
                         * @var BaseModel $this
                         */
                        return $this->country->name;
                    });
                }

                if ($foreignKey = Arr::get($keys, 'state')) {
                    /**
                     * @var BaseModel $item
                     */
                    $item::resolveRelationUsing('state', function ($model) use ($foreignKey) {
                        return $model->belongsTo(State::class, $foreignKey)->withDefault();
                    });

                    MacroableModels::addMacro($item, 'getStateNameAttribute', function () {
                        /**
                         * @var BaseModel $this
                         */
                        return $this->state->name;
                    });
                }

                if ($foreignKey = Arr::get($keys, 'city')) {
                    /**
                     * @var BaseModel $item
                     */
                    $item::resolveRelationUsing('city', function ($model) use ($foreignKey) {
                        return $model->belongsTo(City::class, $foreignKey)->withDefault();
                    });

                    MacroableModels::addMacro($item, 'getCityNameAttribute', function () {
                        /**
                         * @var BaseModel $this
                         */
                        return $this->city->name;
                    });
                }

                MacroableModels::addMacro($item, 'getFullAddressAttribute', function () {
                    /**
                     * @var BaseModel $this
                     */
                    $addresses = [$this->address, $this->city_name, $this->state_name, $this->country_name];

                    return implode(', ', array_filter($addresses));
                });
            }
        });

        $this->app->register(CommandServiceProvider::class);
        $this->app->register(HookServiceProvider::class);
    }
}
