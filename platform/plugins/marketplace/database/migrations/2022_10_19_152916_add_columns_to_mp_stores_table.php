<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->string('zip_code', 20)->nullable();
            $table->string('company', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->dropColumn(['zip_code', 'company']);
        });
    }
};
