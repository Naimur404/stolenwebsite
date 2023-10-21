<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariationItem extends BaseModel
{
    protected $table = 'ec_product_variation_items';

    protected $fillable = [
        'attribute_id',
        'variation_id',
    ];

    public $timestamps = false;

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id')->withDefault();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id')->withDefault();
    }

    public function attributeSet(): HasMany
    {
        return $this->hasMany(ProductAttributeSet::class, 'attribute_set_id');
    }

    public static function getVariationsInfo(array $versionIds)
    {
        return self::query()
            ->join('ec_product_attributes', 'ec_product_attributes.id', '=', 'ec_product_variation_items.attribute_id')
            ->join(
                'ec_product_attribute_sets',
                'ec_product_attribute_sets.id',
                '=',
                'ec_product_attributes.attribute_set_id'
            )
            ->distinct()
            ->whereIn('ec_product_variation_items.variation_id', $versionIds)
            ->select([
                'ec_product_variation_items.variation_id',
                'ec_product_attributes.*',
                'ec_product_attribute_sets.title as attribute_set_title',
                'ec_product_attribute_sets.slug as attribute_set_slug',
            ])
            ->get();
    }

    public static function getProductAttributes(int|string $productId)
    {
        return self::query()
            ->join('ec_product_attributes', 'ec_product_attributes.id', '=', 'ec_product_variation_items.attribute_id')
            ->join(
                'ec_product_attribute_sets',
                'ec_product_attribute_sets.id',
                '=',
                'ec_product_attributes.attribute_set_id'
            )
            ->join('ec_product_variations', 'ec_product_variations.id', '=', 'ec_product_variation_items.variation_id')
            ->distinct()
            ->where('ec_product_variations.product_id', $productId)
            ->select([
                'ec_product_attributes.*',
                'ec_product_attribute_sets.title as attribute_set_title',
                'ec_product_attribute_sets.slug as attribute_set_slug',
            ])
            ->get();
    }
}
