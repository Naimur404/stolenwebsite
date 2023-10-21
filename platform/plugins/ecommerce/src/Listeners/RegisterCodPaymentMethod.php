<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;

class RegisterCodPaymentMethod
{
    public function handle(): void
    {
        PaymentMethods::method(PaymentMethodEnum::COD, [
            'html' => view('plugins/ecommerce::orders.partials.cod')->render(),
            'priority' => 998,
        ]);
    }
}
