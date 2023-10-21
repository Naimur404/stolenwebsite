<?php

namespace Botble\Marketplace\Providers;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Marketplace\Events\WithdrawalRequested;
use Botble\Marketplace\Listeners\OrderCreatedEmailNotification;
use Botble\Marketplace\Listeners\RegisterMarketplaceWidget;
use Botble\Marketplace\Listeners\RenderingSiteMapListener;
use Botble\Marketplace\Listeners\SaveVendorInformationListener;
use Botble\Marketplace\Listeners\WithdrawalRequestedNotification;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SaveVendorInformationListener::class,
        ],
        RenderingSiteMapEvent::class => [
            RenderingSiteMapListener::class,
        ],
        OrderCreated::class => [
            OrderCreatedEmailNotification::class,
        ],
        WithdrawalRequested::class => [
            WithdrawalRequestedNotification::class,
        ],
        RenderingAdminWidgetEvent::class => [
            RegisterMarketplaceWidget::class,
        ],
    ];
}
