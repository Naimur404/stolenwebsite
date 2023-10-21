<?php

namespace Botble\Shippo\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Shippo\Http\Middleware\WebhookMiddleware;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class ShippoServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->setNamespace('plugins/shippo')->loadHelpers();
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->loadAndPublishConfigurations(['general'])
            ->publishAssets();

        $this->app['events']->listen(RouteMatched::class, function () {
            $this->app['router']->aliasMiddleware('shippo.webhook', WebhookMiddleware::class);
        });

        $config = $this->app['config'];
        if (! $config->has('logging.channels.shippo')) {
            $config->set([
                'logging.channels.shippo' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/shippo.log'),
                ],
            ]);
        }

        $this->app->register(HookServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);
    }
}
