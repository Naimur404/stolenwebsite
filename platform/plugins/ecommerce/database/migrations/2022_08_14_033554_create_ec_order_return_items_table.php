<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_order_return_items')) {
            Schema::create('ec_order_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_return_id')->comment('Order return id');
                $table->foreignId('order_product_id')->comment('Order product id');
                $table->foreignId('product_id')->comment('Product id');
                $table->string('product_name');
                $table->integer('qty')->comment('Quantity return');
                $table->decimal('price', 15, 2)->comment('Price Product');
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_order_return_items');
    }
};
