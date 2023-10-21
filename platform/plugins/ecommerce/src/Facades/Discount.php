<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\DiscountSupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Botble\Ecommerce\Supports\DiscountSupport setCustomerId(string|int $customerId)
 * @method static string|int getCustomerId()
 * @method static \Botble\Ecommerce\Models\Discount|null promotionForProduct(array $productIds, array $productCollectionIds)
 * @method static \Illuminate\Support\Collection getAvailablePromotions(bool $forProductSingle = true)
 * @method static void afterOrderPlaced(string $couponCode, string|int|null $customerId = 0)
 * @method static void afterOrderCancelled(string $couponCode, string|int|null $customerId = 0)
 *
 * @see \Botble\Ecommerce\Supports\DiscountSupport
 */
class Discount extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DiscountSupport::class;
    }
}
