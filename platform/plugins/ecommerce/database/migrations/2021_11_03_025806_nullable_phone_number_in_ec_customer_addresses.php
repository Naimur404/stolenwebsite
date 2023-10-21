<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_customer_addresses', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ec_customer_addresses', function (Blueprint $table) {
            $table->string('phone', 20)->change();
        });
    }
};
