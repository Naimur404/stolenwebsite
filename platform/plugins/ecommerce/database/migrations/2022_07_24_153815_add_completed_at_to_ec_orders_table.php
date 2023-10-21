<?php

use Botble\Ecommerce\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_orders', 'completed_at')) {
            Schema::table('ec_orders', function (Blueprint $table) {
                $table->timestamp('completed_at')->after('is_finished')->nullable();
            });
        }

        DB::table('ec_orders')->where('status', OrderStatusEnum::COMPLETED)->update(['completed_at' => Carbon::now()]);
    }

    public function down(): void
    {
        Schema::table('ec_orders', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
