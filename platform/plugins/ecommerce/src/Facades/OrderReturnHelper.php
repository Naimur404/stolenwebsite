<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\OrderReturnHelper as BaseOrderReturnHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array returnOrder(\Botble\Ecommerce\Models\Order $order, array $data)
 * @method static array cancelReturnOrder(\Botble\Ecommerce\Models\OrderReturn $orderReturn)
 * @method static array updateReturnOrder(\Botble\Ecommerce\Models\OrderReturn $orderReturn, array $data)
 *
 * @see \Botble\Ecommerce\Supports\OrderReturnHelper
 */
class OrderReturnHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseOrderReturnHelper::class;
    }
}
