<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_customer_revenues', function (Blueprint $table) {
            $table->decimal('sub_amount', 15)->default(0)->nullable()->change();
            $table->decimal('amount', 15)->default(0)->nullable()->change();
            $table->decimal('current_balance', 15)->default(0)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mp_customer_revenues', function (Blueprint $table) {
            $table->decimal('sub_amount', 15)->default(0)->unsigned()->nullable()->change();
            $table->decimal('amount', 15)->default(0)->unsigned()->nullable()->change();
            $table->decimal('current_balance', 15)->default(0)->unsigned()->nullable()->change();
        });
    }
};
