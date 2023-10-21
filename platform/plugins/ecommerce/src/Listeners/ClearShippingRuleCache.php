<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Shipping;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Services\HandleShippingFeeService;

class ClearShippingRuleCache
{
    public function __construct(protected HandleShippingFeeService $shippingFeeService)
    {
    }

    public function handle(CreatedContentEvent|UpdatedContentEvent|DeletedContentEvent $event): void
    {
        if (! in_array(get_class($event->data), [Shipping::class, ShippingRule::class])) {
            return;
        }

        $this->shippingFeeService->clearCache();
    }
}
