<?php

namespace Botble\Marketplace\Listeners;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Marketplace\Widgets\SaleCommissionHtml;
use Botble\Marketplace\Widgets\StoreRevenueTable;

class RegisterMarketplaceWidget
{
    public function handle(RenderingAdminWidgetEvent $event): void
    {
        $event
            ->widget
            ->register([
                SaleCommissionHtml::class,
                StoreRevenueTable::class,
            ], MARKETPLACE_MODULE_SCREEN_NAME);
    }
}
