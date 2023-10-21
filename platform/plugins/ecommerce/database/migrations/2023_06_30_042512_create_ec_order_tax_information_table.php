<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ec_order_tax_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index();
            $table->string('company_name', 120);
            $table->string('company_address');
            $table->string('company_tax_code', 20);
            $table->string('company_email', 60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_order_tax_information');
    }
};
