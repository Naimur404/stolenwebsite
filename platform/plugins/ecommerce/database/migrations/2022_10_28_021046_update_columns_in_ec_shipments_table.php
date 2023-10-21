<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_shipments', 'metadata')) {
            Schema::table('ec_shipments', function (Blueprint $table) {
                $table->renameColumn('transaction', 'metadata');
                $table->string('rate_id', 120)->after('shipment_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('ec_shipments', function (Blueprint $table) {
            $table->dropColumn('rate_id');
            $table->renameColumn('metadata', 'transaction');
        });
    }
};
