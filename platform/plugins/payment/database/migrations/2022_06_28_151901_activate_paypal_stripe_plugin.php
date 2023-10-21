<?php

use Botble\PluginManagement\Services\PluginService;
use Illuminate\Database\Migrations\Migration;

class ActivatePaypalStripePlugin extends Migration
{
    public function up(): void
    {
        $pluginService = app(PluginService::class);
        $pluginService->activate('stripe');
        $pluginService->activate('paypal');
    }
}
