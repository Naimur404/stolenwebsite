<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_product_tags_translations')) {
            Schema::create('ec_product_tags_translations', function (Blueprint $table) {
                $table->string('lang_code');
                $table->foreignId('ec_product_tags_id');
                $table->string('name')->nullable();

                $table->primary(['lang_code', 'ec_product_tags_id'], 'ec_product_tags_translations_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_product_tags_translations');
    }
};
