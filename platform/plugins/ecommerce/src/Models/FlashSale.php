<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlashSale extends BaseModel
{
    protected $table = 'ec_flash_sales';

    protected $fillable = [
        'name',
        'end_date',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'end_date' => 'datetime',
        'name' => SafeContent::class,
    ];

    public function products(): BelongsToMany
    {
        return $this
            ->belongsToMany(Product::class, 'ec_flash_sale_products', 'flash_sale_id', 'product_id')
            ->withPivot(['price', 'quantity', 'sold']);
    }

    public function getEndDateAttribute($value): string|null
    {
        if (! $value) {
            return $value;
        }

        return Carbon::parse($value)->format('Y/m/d');
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->whereDate('end_date', '>', Carbon::now()->toDateString());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('end_date', '=<', Carbon::now()->toDateString());
    }

    protected function expired(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                return Carbon::parse($this->end_date)->lessThan(Carbon::now());
            },
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (FlashSale $flashSale) {
            $flashSale->products()->detach();
        });
    }
}
