<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\FlashSaleSupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Botble\Ecommerce\Models\Product|null flashSaleForProduct(\Botble\Ecommerce\Models\Product $product)
 * @method static \Illuminate\Support\Collection getAvailableFlashSales()
 *
 * @see \Botble\Ecommerce\Supports\FlashSaleSupport
 */
class FlashSale extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FlashSaleSupport::class;
    }
}
