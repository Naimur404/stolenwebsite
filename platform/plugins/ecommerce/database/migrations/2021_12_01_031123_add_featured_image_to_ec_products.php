<?php

use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_products', 'image')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->string('image', 255)->nullable();
            });

            foreach (Product::query()->where('is_variation', 0)->get() as $product) {
                $product->image = Arr::first($product->images) ?: null;
                $product->save();
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_products', 'image')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
