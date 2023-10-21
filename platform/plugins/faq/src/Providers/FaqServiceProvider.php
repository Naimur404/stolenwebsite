<?php

namespace Botble\Faq\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Faq\Contracts\Faq as FaqContract;
use Botble\Faq\FaqSupport;
use Botble\Faq\Models\Faq;
use Botble\Faq\Models\FaqCategory;
use Botble\Faq\Repositories\Eloquent\FaqCategoryRepository;
use Botble\Faq\Repositories\Eloquent\FaqRepository;
use Botble\Faq\Repositories\Interfaces\FaqCategoryInterface;
use Botble\Faq\Repositories\Interfaces\FaqInterface;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Routing\Events\RouteMatched;

class FaqServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(FaqCategoryInterface::class, function () {
            return new FaqCategoryRepository(new FaqCategory());
        });

        $this->app->bind(FaqInterface::class, function () {
            return new FaqRepository(new Faq());
        });

        $this->app->singleton(FaqContract::class, FaqSupport::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/faq')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->publishAssets();

        if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            LanguageAdvancedManager::registerModule(Faq::class, [
                'question',
                'answer',
            ]);
            LanguageAdvancedManager::registerModule(FaqCategory::class, [
                'name',
            ]);
        }

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-faq',
                    'priority' => 5,
                    'parent_id' => null,
                    'name' => 'plugins/faq::faq.name',
                    'icon' => 'far fa-question-circle',
                    'url' => route('faq.index'),
                    'permissions' => ['faq.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-faq-list',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-faq',
                    'name' => 'plugins/faq::faq.all',
                    'icon' => null,
                    'url' => route('faq.index'),
                    'permissions' => ['faq.index'],
                ])
                ->registerItem([
                    'id' => 'cms-packages-faq-category',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-faq',
                    'name' => 'plugins/faq::faq-category.name',
                    'icon' => null,
                    'url' => route('faq_category.index'),
                    'permissions' => ['faq_category.index'],
                ]);
        });

        $this->app->register(HookServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }
}
