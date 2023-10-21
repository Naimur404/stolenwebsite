<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_orders', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });

        Schema::table('ec_shipping_rules', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }

    public function down(): void
    {
        Schema::table('ec_orders', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable();
        });

        Schema::table('ec_shipping_rules', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable();
        });
    }
};
