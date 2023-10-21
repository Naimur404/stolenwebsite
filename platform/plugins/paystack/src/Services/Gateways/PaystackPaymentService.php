<?php

namespace Botble\Paystack\Services\Gateways;

use Botble\Paystack\Services\Abstracts\PaystackPaymentAbstract;
use Illuminate\Http\Request;

class PaystackPaymentService extends PaystackPaymentAbstract
{
    public function makePayment(Request $request)
    {
    }

    public function afterMakePayment(Request $request)
    {
    }

    /**
     * List currencies supported https://support.paystack.com/hc/en-us/articles/360009973779
     */
    public function supportedCurrencyCodes(): array
    {
        return [
            'NGN',
            'GHS',
            'USD',
            'ZAR',
        ];
    }
}
