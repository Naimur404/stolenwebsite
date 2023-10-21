<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_customer_withdrawals', function (Blueprint $table) {
            $table->string('transaction_id', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mp_customer_withdrawals', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
