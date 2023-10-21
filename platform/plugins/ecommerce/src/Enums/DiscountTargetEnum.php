<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static DiscountTargetEnum ALL_ORDERS()
 * @method static DiscountTargetEnum CUSTOMER()
 * @method static DiscountTargetEnum MINIMUM_ORDER_AMOUNT()
 * @method static DiscountTargetEnum ONCE_PER_CUSTOMER()
 * @method static DiscountTargetEnum PRODUCT_VARIANT()
 * @method static DiscountTargetEnum PRODUCT_COLLECTIONS()
 * @method static DiscountTargetEnum SPECIFIC_PRODUCT()
 */
class DiscountTargetEnum extends Enum
{
    public const ALL_ORDERS = 'all-orders';
    public const CUSTOMER = 'customer';
    public const MINIMUM_ORDER_AMOUNT = 'amount-minimum-order';
    public const ONCE_PER_CUSTOMER = 'once-per-customer';
    public const PRODUCT_VARIANT = 'product-variant';
    public const PRODUCT_COLLECTIONS = 'group-products';
    public const SPECIFIC_PRODUCT = 'specific-product';

    public static $langPath = 'plugins/ecommerce::discount.enums.targets';
}
