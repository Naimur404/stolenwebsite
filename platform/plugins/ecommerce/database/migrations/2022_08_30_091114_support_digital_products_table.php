<?php

use Botble\Ecommerce\Enums\ProductTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_products', 'product_type')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->string('product_type', 60)->nullable()->default(ProductTypeEnum::PHYSICAL);
            });
        }

        if (! Schema::hasTable('ec_product_files')) {
            Schema::create('ec_product_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->index();
                $table->string('url', 400)->nullable();
                $table->mediumText('extras')->nullable(); // file name, size, mime_type...
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('ec_order_product', 'product_type')) {
            Schema::table('ec_order_product', function (Blueprint $table) {
                $table->string('product_type', 60)->default(ProductTypeEnum::PHYSICAL);
                $table->integer('times_downloaded')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('ec_order_product', function (Blueprint $table) {
            $table->dropColumn(['times_downloaded', 'product_type']);
        });

        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropColumn(['product_type']);
        });

        Schema::dropIfExists('ec_product_files');
    }
};
