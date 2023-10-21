<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_order_addresses', function (Blueprint $table) {
            $table->string('type', 60)->default('shipping_address');
        });
    }

    public function down(): void
    {
        Schema::table('ec_order_addresses', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
