<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('ec_product_attributes', 'status')) {
            Schema::table('ec_product_attributes', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('ec_product_attributes', 'status')) {
            Schema::table('ec_product_attributes', function (Blueprint $table) {
                $table->string('status', 60)->default('published');
            });
        }
    }
};
