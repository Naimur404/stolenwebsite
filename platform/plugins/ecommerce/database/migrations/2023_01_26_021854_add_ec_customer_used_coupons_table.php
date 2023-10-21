<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_customer_used_coupons')) {
            Schema::create('ec_customer_used_coupons', function (Blueprint $table) {
                $table->foreignId('discount_id');
                $table->foreignId('customer_id');
                $table->primary(['discount_id', 'customer_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_customer_used_coupons');
    }
};
