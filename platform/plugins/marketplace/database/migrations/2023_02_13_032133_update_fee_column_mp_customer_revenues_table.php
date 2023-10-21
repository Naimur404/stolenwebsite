<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mp_customer_revenues')) {
            Schema::table('mp_customer_revenues', function (Blueprint $table) {
                $table->decimal('fee', 15)->default(0)->nullable()->change();
            });
        }
    }
};
