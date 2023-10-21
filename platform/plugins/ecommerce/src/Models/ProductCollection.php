<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Botble\Ecommerce\Enums\DiscountTargetEnum;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductCollection extends BaseModel
{
    use HasSlug;

    protected $table = 'ec_product_collections';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    protected static function booted(): void
    {
        self::saving(function (self $model) {
            $model->slug = self::createSlug($model->slug ?: $model->name, $model->getKey());
        });

        self::deleting(function (ProductCollection $collection) {
            $collection->discounts()->detach();
        });
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'ec_discount_customers', 'customer_id', 'id');
    }

    public function products(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Product::class,
                'ec_product_collection_products',
                'product_collection_id',
                'product_id'
            )
            ->where('is_variation', 0);
    }

    public function promotions(): BelongsToMany
    {
        return $this
            ->belongsToMany(Discount::class, 'ec_discount_product_collections', 'product_collection_id')
            ->where('type', DiscountTypeEnum::PROMOTION)
            ->where('start_date', '<=', Carbon::now())
            ->where('target', DiscountTargetEnum::PRODUCT_COLLECTIONS)
            ->where(function ($query) {
                return $query
                    ->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now());
            })
            ->where('product_quantity', 1);
    }
}
