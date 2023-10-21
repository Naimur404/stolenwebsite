<?php

use Botble\Ecommerce\Models\ProductCategory;
use Botble\Menu\Models\MenuNode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        try {
            $categoryIds = DB::table('ec_product_categories')->pluck('id')->all();

            DB::table('ec_product_categories')
                ->whereColumn('id', 'parent_id')
                ->orWhere(function (Builder $query) use ($categoryIds) {
                    $query
                        ->whereNotNull('parent_id')
                        ->whereNot('parent_id', '=', 0)
                        ->whereNotIn('parent_id', $categoryIds)
                        ->whereNotIn(
                            'id',
                            MenuNode::query()->where('reference_type', ProductCategory::class)->pluck('reference_id')->all()
                        );
                })
                ->delete();

            DB::table('ec_product_categories_translations')
                ->whereNotIn('ec_product_categories_id', $categoryIds)
                ->delete();
        } catch (Exception $exception) {
            info($exception->getMessage());
        }
    }
};
