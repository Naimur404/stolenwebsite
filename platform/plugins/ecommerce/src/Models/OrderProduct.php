<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class OrderProduct extends BaseModel
{
    protected $table = 'ec_order_product';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_image',
        'qty',
        'weight',
        'price',
        'tax_amount',
        'options',
        'product_options',
        'restock_quantity',
        'product_type',
        'license_code',
    ];

    protected $casts = [
        'options' => 'json',
        'product_options' => 'json',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withDefault();
    }

    public function getAmountFormatAttribute(): string
    {
        return format_price($this->price);
    }

    public function getTotalFormatAttribute(): string
    {
        return format_price($this->price * $this->qty);
    }

    public function productFiles(): HasMany
    {
        return $this->hasMany(ProductFile::class, 'product_id', 'product_id');
    }

    public function productFileExternalCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->productFiles->filter(fn (ProductFile $file) => $file->is_external_link)->count(),
        );
    }

    public function productFileInternalCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->productFiles->filter(fn (ProductFile $file) => ! $file->is_external_link)->count(),
        );
    }

    public function isTypeDigital(): bool
    {
        return isset($this->attributes['product_type']) && $this->attributes['product_type'] == ProductTypeEnum::DIGITAL;
    }

    protected function downloadToken(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->isTypeDigital() ? ($this->order->id . '-' . $this->order->token . '-' . $this->id) : null
        );
    }

    protected function downloadHash(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->download_token ? Hash::make($this->download_token) : null
        );
    }

    protected function downloadHashUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->download_hash ? route('public.digital-products.download', [
                'id' => $this->id,
                'hash' => $this->download_hash,
            ]) : null
        );
    }

    protected function downloadExternalUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->download_hash ? route('public.digital-products.download', [
                'id' => $this->id,
                'hash' => $this->download_hash,
                'external' => true,
            ]) : null
        );
    }

    protected function priceWithTax(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price + $this->tax_amount
        );
    }

    protected function totalPriceWithTax(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_with_tax * $this->qty
        );
    }

    public function productOptionsImplode(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->product_options) {
                    return '';
                }
                $options = $this->product_options;

                return '(' . implode(', ', Arr::map(Arr::get($options, 'optionInfo'), function ($item, $key) use ($options) {
                    return implode(': ', [
                        $item,
                        Arr::get($options, 'optionCartValue.' . $key . '.0.option_value'),
                    ]);
                })) . ')';
            }
        );
    }
}
