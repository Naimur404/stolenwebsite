<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ec_product_categorizables', function (Blueprint $table) {
            $table->foreignId('category_id')->index();
            $table->foreignId('reference_id')->index();
            $table->string('reference_type', 120)->index();
            $table->primary(['category_id', 'reference_id', 'reference_type'], 'ec_product_categorizables_primary_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_product_categorizables');
    }
};
