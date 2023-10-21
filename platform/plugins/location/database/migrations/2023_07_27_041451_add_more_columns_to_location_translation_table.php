<?php

use Botble\Location\Models\City;
use Botble\Location\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('cities_translations', 'slug')) {
            Schema::table('cities_translations', function (Blueprint $table) {
                $table->string('slug', 120)->after('name')->nullable();
            });
        }

        City::query()->whereNull('slug')->get()->each(function (City $city) {
            $city->slug = $city->name;
            $city->save();
        });

        if (! Schema::hasColumn('states_translations', 'slug')) {
            Schema::table('states_translations', function (Blueprint $table) {
                $table->string('slug', 120)->after('name')->nullable();
            });
        }

        State::query()->whereNull('slug')->get()->each(function (State $state) {
            $state->slug = $state->name;
            $state->save();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('cities_translations', 'slug')) {
            Schema::table('cities_translations', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }

        if (Schema::hasColumn('states_translations', 'slug')) {
            Schema::table('states_translations', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
