<?php

namespace Botble\SslCommerz\Services\Gateways;

use Botble\SslCommerz\Services\Abstracts\SslCommerzPaymentAbstract;
use Illuminate\Http\Request;

class SslCommerzPaymentService extends SslCommerzPaymentAbstract
{
    public function makePayment(Request $request)
    {
    }

    public function afterMakePayment(Request $request)
    {
    }
}
