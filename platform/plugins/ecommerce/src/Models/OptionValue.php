<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Option\OptionType\Field;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionValue extends BaseModel
{
    protected $table = 'ec_option_value';

    protected $fillable = [
        'option_id',
        'option_value',
        'affect_price',
        'affect_type',
        'order',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'option_id');
    }

    protected function formatPrice(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                return format_price($this->price);
            },
        );
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: function (): float|int {
                $option = $this->option;
                if ($option->option_type == Field::class) {
                    return 0;
                }

                $product = $option->product;

                $price = $this->affect_type == 0 ? $this->affect_price : (floatval($this->affect_price) * $product->original_price) / 100;

                return $price;
            },
        );
    }
}
