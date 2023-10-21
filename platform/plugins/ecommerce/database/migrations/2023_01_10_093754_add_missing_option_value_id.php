<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_option_value', function (Blueprint $table) {
            $table->id()->after('option_id');
        });

        Schema::table('ec_global_option_value', function (Blueprint $table) {
            $table->id()->after('option_id');
        });
    }

    public function down(): void
    {
        Schema::table('ec_global_option_value', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('ec_option_value', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
