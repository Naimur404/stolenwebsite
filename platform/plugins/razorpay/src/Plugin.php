<?php

namespace Botble\Razorpay;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'payment_razorpay_name',
            'payment_razorpay_description',
            'payment_razorpay_key',
            'payment_razorpay_secret',
            'payment_razorpay_status',
        ]);
    }
}
