<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropColumn([
                'options',
                'is_searchable',
                'is_show_on_list',
                'barcode',
                'category_id',
                'length_unit',
                'wide_unit',
                'height_unit',
                'weight_unit',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->text('options')->nullable();
            $table->tinyInteger('is_searchable')->default(0);
            $table->tinyInteger('is_show_on_list')->default(0);
            $table->string('barcode')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->string('length_unit', 20)->nullable();
            $table->string('wide_unit', 20)->nullable();
            $table->string('height_unit', 20)->nullable();
            $table->string('weight_unit', 20)->nullable();
        });
    }
};
