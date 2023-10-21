<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Cart\Cart as BaseCart;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Botble\Ecommerce\Cart\Cart instance(string|null $instance = null)
 * @method static \Carbon\CarbonInterface|null getLastUpdatedAt()
 * @method static array|\Botble\Ecommerce\Cart\CartItem add(mixed $id, mixed $name = null, int|float $qty = null, float $price = null, array $options = [])
 * @method static int|float getPriceByOptions(int|float $price, array $options = [])
 * @method static \Botble\Ecommerce\Cart\Cart putToSession($content)
 * @method static void setLastUpdatedAt()
 * @method static \Botble\Ecommerce\Cart\CartItem|bool update(string $rowId, mixed $qty)
 * @method static \Botble\Ecommerce\Cart\CartItem|null get(string $rowId)
 * @method static void remove(string $rowId)
 * @method static void destroy()
 * @method static int|float count()
 * @method static int|float countByItems($content)
 * @method static int rawTotal()
 * @method static int rawTotalByItems($content)
 * @method static float rawTaxByItems($content)
 * @method static float rawSubTotal()
 * @method static float rawSubTotalByItems($content)
 * @method static \Illuminate\Support\Collection search(\Closure $search)
 * @method static void associate(string $rowId, mixed $model)
 * @method static void setTax(string $rowId, int|float $taxRate)
 * @method static void store(mixed $identifier)
 * @method static string currentInstance()
 * @method static void restore(mixed $identifier)
 * @method static string total()
 * @method static float|string tax()
 * @method static float rawTax()
 * @method static string subtotal()
 * @method static \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection products()
 * @method static \Illuminate\Support\Collection content()
 * @method static int|float weight()
 *
 * @see \Botble\Ecommerce\Cart\Cart
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseCart::class;
    }
}
