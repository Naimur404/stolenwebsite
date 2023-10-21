<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Option extends BaseModel
{
    protected $table = 'ec_options';

    protected $fillable = [
        'name',
        'option_type',
        'required',
        'product_id',
        'order',
    ];

    public function values(): HasMany
    {
        return $this
            ->hasMany(OptionValue::class, 'option_id')
            ->orderBy('order');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        self::deleting(function (Option $option) {
            $option->values()->delete();
        });
    }
}
