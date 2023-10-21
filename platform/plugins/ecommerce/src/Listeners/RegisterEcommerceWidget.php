<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Ecommerce\Widgets\CustomerChart;
use Botble\Ecommerce\Widgets\NewCustomerCard;
use Botble\Ecommerce\Widgets\NewOrderCard;
use Botble\Ecommerce\Widgets\NewProductCard;
use Botble\Ecommerce\Widgets\OrderChart;
use Botble\Ecommerce\Widgets\RecentOrdersTable;
use Botble\Ecommerce\Widgets\ReportGeneralHtml;
use Botble\Ecommerce\Widgets\RevenueCard;
use Botble\Ecommerce\Widgets\TopSellingProductsTable;
use Botble\Ecommerce\Widgets\TrendingProductsTable;

class RegisterEcommerceWidget
{
    public function handle(RenderingAdminWidgetEvent $event): void
    {
        $event->widget
            ->register([
                RevenueCard::class,
                NewProductCard::class,
                NewCustomerCard::class,
                NewOrderCard::class,
                CustomerChart::class,
                OrderChart::class,
                ReportGeneralHtml::class,
                RecentOrdersTable::class,
                TopSellingProductsTable::class,
                TrendingProductsTable::class,
            ], 'ecommerce');
    }
}
