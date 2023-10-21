<?php

use Botble\Setting\Facades\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration
{
    public function up(): void
    {
        Setting::set(['api_layer_api_key' => get_ecommerce_setting('currencies_api_key')]);
    }

    public function down(): void
    {
        Setting::set(['currencies_api_key' => get_ecommerce_setting('api_layer_api_key')]);
    }
};
