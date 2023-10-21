<?php

namespace Botble\SimpleSlider\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Language\Facades\Language;
use Botble\SimpleSlider\Models\SimpleSlider;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Botble\SimpleSlider\Repositories\Eloquent\SimpleSliderItemRepository;
use Botble\SimpleSlider\Repositories\Eloquent\SimpleSliderRepository;
use Botble\SimpleSlider\Repositories\Interfaces\SimpleSliderInterface;
use Botble\SimpleSlider\Repositories\Interfaces\SimpleSliderItemInterface;
use Illuminate\Routing\Events\RouteMatched;

class SimpleSliderServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(SimpleSliderInterface::class, function () {
            return new SimpleSliderRepository(new SimpleSlider());
        });

        $this->app->bind(SimpleSliderItemInterface::class, function () {
            return new SimpleSliderItemRepository(new SimpleSliderItem());
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/simple-slider')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadMigrations()
            ->publishAssets();

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-simple-slider',
                'priority' => 100,
                'parent_id' => null,
                'name' => 'plugins/simple-slider::simple-slider.menu',
                'icon' => 'far fa-image',
                'url' => route('simple-slider.index'),
                'permissions' => ['simple-slider.index'],
            ]);
        });

        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            Language::registerModule(SimpleSlider::class);
        }

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });
    }
}
