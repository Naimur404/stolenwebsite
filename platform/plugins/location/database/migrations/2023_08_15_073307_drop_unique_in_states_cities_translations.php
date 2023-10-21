<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('states_translations', 'slug')) {
            Schema::table('states_translations', function (Blueprint $table) {
                $table->string('slug', 120)->after('name')->nullable()->change();
            });
        }

        if (Schema::hasColumn('cities_translations', 'slug')) {
            Schema::table('cities_translations', function (Blueprint $table) {
                $table->string('slug', 120)->after('name')->nullable()->change();
            });
        }

        try {
            Schema::table('states_translations', function (Blueprint $table) {
                $table->dropUnique('states_translations_slug_unique');
            });

            Schema::table('cities_translations', function (Blueprint $table) {
                $table->dropUnique('cities_translations_slug_unique');
            });
        } catch (Throwable) {}
    }
};
