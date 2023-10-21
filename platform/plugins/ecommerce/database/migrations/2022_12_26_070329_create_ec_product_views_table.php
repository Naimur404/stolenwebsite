<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ec_product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index();
            $table->integer('views')->default(1);
            $table->date('date')->default(Carbon::now());

            $table->unique(['product_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_product_views');
    }
};
