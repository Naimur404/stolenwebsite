<?php

namespace Botble\Payment;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('payments');

        Setting::delete([
            'default_payment_method',
            'payment_cod_status',
            'payment_cod_description',
            'payment_cod_name',
            'payment_bank_transfer_status',
            'payment_bank_transfer_description',
            'payment_bank_transfer_name',
        ]);
    }
}
