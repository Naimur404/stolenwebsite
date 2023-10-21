<?php

use Botble\Location\Models\City;
use Botble\Location\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('cities', 'slug')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->string('slug', 120)->unique()->after('name')->nullable();
            });
        }

        if (! Schema::hasColumn('cities', 'image')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->string('image', 255)->after('order')->nullable();
            });
        }

        if (! Schema::hasColumn('states', 'slug')) {
            Schema::table('states', function (Blueprint $table) {
                $table->string('slug', 120)->unique()->after('name')->nullable();
            });
        }

        if (! Schema::hasColumn('states', 'image')) {
            Schema::table('states', function (Blueprint $table) {
                $table->string('image', 255)->after('order')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cities', 'image')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }

        if (Schema::hasColumn('cities', 'slug')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }

        if (Schema::hasColumn('states', 'image')) {
            Schema::table('states', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }

        if (Schema::hasColumn('states', 'slug')) {
            Schema::table('states', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
