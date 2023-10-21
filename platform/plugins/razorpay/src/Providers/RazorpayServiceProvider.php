<?php

namespace Botble\Razorpay\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class RazorpayServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this->setNamespace('plugins/razorpay')
            ->loadHelpers()
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadRoutes();

        $this->app->register(HookServiceProvider::class);
    }
}
