<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('ec_reviews', 'images')) {
                $table->text('images')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ec_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('ec_reviews', 'images')) {
                $table->dropColumn('images');
            }
        });
    }
};
