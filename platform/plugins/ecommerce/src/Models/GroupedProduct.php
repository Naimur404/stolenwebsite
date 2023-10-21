<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class GroupedProduct extends BaseModel
{
    protected $table = 'ec_grouped_products';

    protected $fillable = [
        'parent_product_id',
        'product_id',
        'fixed_qty',
    ];

    public $timestamps = false;

    public static function getChildren(int|string $groupedProductId)
    {
        return self::query()
            ->join('ec_products', 'ec_products.id', '=', 'ec_grouped_products.parent_product_id')
            ->whereIn('ec_products.id', [$groupedProductId])
            ->distinct()
            ->get();
    }

    public static function createGroupedProducts(int|string $groupedProductId, array $childItems)
    {
        DB::beginTransaction();

        self::query()
            ->where('parent_product_id', $groupedProductId)
            ->delete();

        foreach ($childItems as $item) {
            self::query()->create([
                'parent_product_id' => $groupedProductId,
                'product_id' => $item['id'],
                'fixed_qty' => isset($item['qty']) & $item['qty'] ?: 1,
            ]);
        }

        DB::commit();

        return true;
    }
}
