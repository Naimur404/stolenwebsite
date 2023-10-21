<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tax extends BaseModel
{
    protected $table = 'ec_taxes';

    protected $fillable = [
        'title',
        'percentage',
        'priority',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ec_tax_products', 'tax_id', 'product_id');
    }

    protected function defaultTitle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_default ? (' - ' . trans('plugins/ecommerce::tax.default')) : '',
        );
    }

    protected function titleWithPercentage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->title . ' (' . $this->percentage . '%)' . $this->default_title,
        );
    }

    protected function isDefault(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id == get_ecommerce_setting('default_tax_rate'),
        );
    }

    protected static function booted(): void
    {
        self::deleting(function (Tax $tax) {
            $tax->products()->detach();
        });
    }
}
