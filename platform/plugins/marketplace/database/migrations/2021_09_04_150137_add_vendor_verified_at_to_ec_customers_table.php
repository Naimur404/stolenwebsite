<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('ec_customers', 'vendor_verified_at')) {
                $table->dateTime('vendor_verified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ec_customers', function (Blueprint $table) {
            if (Schema::hasColumn('ec_customers', 'vendor_verified_at')) {
                $table->dropColumn('vendor_verified_at');
            }
        });
    }
};
