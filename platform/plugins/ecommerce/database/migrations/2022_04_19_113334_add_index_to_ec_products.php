<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->index(['brand_id', 'status', 'is_variation', 'created_at']);
        });

        Schema::table('ec_reviews', function (Blueprint $table) {
            $table->index(['product_id', 'customer_id', 'status', 'created_at']);
        });

        Schema::table('ec_product_categories', function (Blueprint $table) {
            $table->index(['parent_id', 'status', 'created_at']);
        });

        Schema::table('ec_orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropIndex(['brand_id', 'status', 'is_variation', 'created_at']);
        });

        Schema::table('ec_reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'customer_id', 'status', 'created_at']);
        });

        Schema::table('ec_product_categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'status', 'created_at']);
        });

        Schema::table('ec_orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status', 'created_at']);
        });
    }
};
