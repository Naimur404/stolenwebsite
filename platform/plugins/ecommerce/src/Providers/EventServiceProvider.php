<?php

namespace Botble\Ecommerce\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Events\OrderReturnedEvent;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Events\ProductViewed;
use Botble\Ecommerce\Events\ShippingStatusChanged;
use Botble\Ecommerce\Listeners\AddLanguageForVariantsListener;
use Botble\Ecommerce\Listeners\ClearShippingRuleCache;
use Botble\Ecommerce\Listeners\GenerateInvoiceListener;
use Botble\Ecommerce\Listeners\GenerateLicenseCodeAfterOrderCompleted;
use Botble\Ecommerce\Listeners\OrderCancelledNotification;
use Botble\Ecommerce\Listeners\OrderCreatedNotification;
use Botble\Ecommerce\Listeners\OrderPaymentConfirmedNotification;
use Botble\Ecommerce\Listeners\OrderReturnedNotification;
use Botble\Ecommerce\Listeners\RegisterCodPaymentMethod;
use Botble\Ecommerce\Listeners\RegisterEcommerceWidget;
use Botble\Ecommerce\Listeners\RenderingSiteMapListener;
use Botble\Ecommerce\Listeners\SendMailsAfterCustomerRegistered;
use Botble\Ecommerce\Listeners\SendProductReviewsMailAfterOrderCompleted;
use Botble\Ecommerce\Listeners\SendShippingStatusChangedNotification;
use Botble\Ecommerce\Listeners\SendWebhookWhenOrderPlaced;
use Botble\Ecommerce\Listeners\UpdateProductStockStatus;
use Botble\Ecommerce\Listeners\UpdateProductView;
use Botble\Payment\Events\RenderingPaymentMethods;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RenderingSiteMapEvent::class => [
            RenderingSiteMapListener::class,
        ],
        CreatedContentEvent::class => [
            AddLanguageForVariantsListener::class,
            ClearShippingRuleCache::class,
        ],
        UpdatedContentEvent::class => [
            AddLanguageForVariantsListener::class,
            ClearShippingRuleCache::class,
        ],
        DeletedContentEvent::class => [
            ClearShippingRuleCache::class,
        ],
        Registered::class => [
            SendMailsAfterCustomerRegistered::class,
        ],
        OrderPlacedEvent::class => [
            SendWebhookWhenOrderPlaced::class,
            GenerateInvoiceListener::class,
            OrderCreatedNotification::class,
        ],
        OrderCreated::class => [
            GenerateInvoiceListener::class,
            OrderCreatedNotification::class,
        ],
        ProductQuantityUpdatedEvent::class => [
            UpdateProductStockStatus::class,
        ],
        OrderCompletedEvent::class => [
            SendProductReviewsMailAfterOrderCompleted::class,
            GenerateLicenseCodeAfterOrderCompleted::class,
        ],
        ProductViewed::class => [
            UpdateProductView::class,
        ],
        ShippingStatusChanged::class => [
            SendShippingStatusChangedNotification::class,
        ],
        RenderingAdminWidgetEvent::class => [
            RegisterEcommerceWidget::class,
        ],
        OrderPaymentConfirmedEvent::class => [
            OrderPaymentConfirmedNotification::class,
        ],
        OrderCancelledEvent::class => [
            OrderCancelledNotification::class,
        ],
        OrderReturnedEvent::class => [
            OrderReturnedNotification::class,
        ],
        RenderingPaymentMethods::class => [
            RegisterCodPaymentMethod::class,
        ],
    ];
}
