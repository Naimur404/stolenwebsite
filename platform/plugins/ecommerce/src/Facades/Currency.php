<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\CurrencySupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void setApplicationCurrency(\Botble\Ecommerce\Models\Currency $currency)
 * @method static \Botble\Ecommerce\Models\Currency|null getApplicationCurrency()
 * @method static \Botble\Ecommerce\Models\Currency|null getDefaultCurrency()
 * @method static \Illuminate\Support\Collection currencies()
 * @method static string|null detectedCurrencyCode()
 * @method static array countryCurrencies()
 * @method static array currencyCodes()
 *
 * @see \Botble\Ecommerce\Supports\CurrencySupport
 */
class Currency extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CurrencySupport::class;
    }
}
