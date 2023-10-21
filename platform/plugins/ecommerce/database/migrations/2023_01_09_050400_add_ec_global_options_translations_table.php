<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('ec_global_options_translations');
        Schema::dropIfExists('ec_options_translations');
        Schema::dropIfExists('ec_option_value_translations');
        Schema::dropIfExists('ec_global_option_value_translations');

        Schema::create('ec_global_options_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->foreignId('ec_global_options_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'ec_global_options_id'], 'ec_global_options_translations_primary');
        });

        Schema::create('ec_options_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->foreignId('ec_options_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'ec_options_id'], 'ec_options_translations_primary');
        });

        Schema::create('ec_option_value_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->foreignId('ec_option_value_id');
            $table->string('option_value', 255)->nullable();

            $table->primary(['lang_code', 'ec_option_value_id'], 'ec_option_value_translations_primary');
        });

        Schema::create('ec_global_option_value_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->foreignId('ec_global_option_value_id');
            $table->string('option_value', 255)->nullable();

            $table->primary(['lang_code', 'ec_global_option_value_id'], 'ec_global_option_value_translations_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_global_options_translations');
        Schema::dropIfExists('ec_options_translations');
        Schema::dropIfExists('ec_option_value_translations');
        Schema::dropIfExists('ec_global_option_value_translations');
    }
};
