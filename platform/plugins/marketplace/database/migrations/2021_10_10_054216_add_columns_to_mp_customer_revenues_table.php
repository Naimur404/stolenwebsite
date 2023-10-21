<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_customer_revenues', function (Blueprint $table) {
            $table->foreignId('user_id')->default(0);
            $table->string('type', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mp_customer_revenues', function (Blueprint $table) {
            $table->dropColumn('user_id', 'type');
        });
    }
};
