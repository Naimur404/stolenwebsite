<?php

use Botble\Ecommerce\Models\Review;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        try {
            DB::beginTransaction();

            $reviews = Review::query()
                ->selectRaw(DB::raw('max(id) as id, product_id, customer_id'))
                ->groupBy('product_id', 'customer_id')
                ->pluck('id')
                ->all();

            Review::query()->whereNotIn('id', $reviews)->delete();

            Schema::table('ec_reviews', function (Blueprint $table) {
                $table->unique(['product_id', 'customer_id']);
            });

            DB::commit();
        } catch (Throwable) {
            DB::rollBack();
        }
    }

    public function down(): void
    {
        Schema::table('ec_reviews', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'customer_id']);
        });
    }
};
