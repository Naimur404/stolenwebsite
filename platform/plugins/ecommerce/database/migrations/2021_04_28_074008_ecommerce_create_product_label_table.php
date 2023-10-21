<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ec_product_labels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('color', 120)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('ec_product_label_products', function (Blueprint $table) {
            $table->foreignId('product_label_id')->index();
            $table->foreignId('product_id')->index();
            $table->primary(['product_label_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_product_label_products');
        Schema::dropIfExists('ec_product_labels');
    }
};
