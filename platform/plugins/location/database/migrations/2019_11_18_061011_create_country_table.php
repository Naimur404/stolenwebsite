<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('nationality', 120);
                $table->tinyInteger('order')->default(0);
                $table->tinyInteger('is_default')->unsigned()->default(0);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('abbreviation', 10)->nullable();
                $table->foreignId('country_id')->nullable();
                $table->tinyInteger('order')->default(0);
                $table->tinyInteger('is_default')->unsigned()->default(0);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->foreignId('state_id');
                $table->foreignId('country_id')->nullable();
                $table->string('record_id', 40)->nullable();
                $table->tinyInteger('order')->default(0);
                $table->tinyInteger('is_default')->unsigned()->default(0);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
