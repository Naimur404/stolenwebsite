<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ec_product_collection_products', 'id')) {
            return;
        }

        try {
            Schema::disableForeignKeyConstraints();

            Schema::table('ec_product_collection_products', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        } catch (Throwable) {}
    }
};
