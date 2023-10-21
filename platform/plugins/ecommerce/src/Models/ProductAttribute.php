<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Media\Facades\RvMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ProductAttribute extends BaseModel
{
    protected $table = 'ec_product_attributes';

    protected $fillable = [
        'title',
        'slug',
        'color',
        'order',
        'attribute_set_id',
        'image',
        'is_default',
    ];

    public function getAttributeSetIdAttribute(int|string|null $value): int|string|null
    {
        return $value;
    }

    public function productAttributeSet(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeSet::class, 'attribute_set_id');
    }

    public function getGroupIdAttribute(int|string|null $value): int|string|null
    {
        return $value;
    }

    protected static function booted(): void
    {
        self::deleting(function (ProductAttribute $productAttribute) {
            $productAttribute->productVariationItems()->delete();
        });
    }

    public function productVariationItems(): HasMany
    {
        return $this->hasMany(ProductVariationItem::class, 'attribute_id');
    }

    public function getAttributeStyle(?ProductAttributeSet $attributeSet = null, array|Collection $productVariations = []): string
    {
        if ($attributeSet && $attributeSet->use_image_from_product_variation) {
            foreach ($productVariations as $productVariation) {
                $attribute = $productVariation->productAttributes->where('attribute_set_id', $attributeSet->id)->first();
                if ($attribute && $attribute->id == $this->id && ($image = $productVariation->product->image)) {
                    return 'background-image: url(' . RvMedia::getImageUrl($image) . '); background-size: cover; background-repeat: no-repeat; background-position: center;';
                }
            }
        }

        if ($this->image) {
            return 'background-image: url(' . RvMedia::getImageUrl($this->image) . '); background-size: cover; background-repeat: no-repeat; background-position: center;';
        }

        return 'background-color: ' . ($this->color ?: '#000') . ';';
    }
}
