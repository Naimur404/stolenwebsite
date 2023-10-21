<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Traits\LocationTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipping extends BaseModel
{
    use LocationTrait;

    protected $table = 'ec_shipping';

    protected $fillable = [
        'title',
        'country',
    ];

    protected static function booted(): void
    {
        self::deleting(function (Shipping $shipping) {
            $shipping->rules()->each(fn (ShippingRule $rule) => $rule->delete());
        });
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ShippingRule::class, 'shipping_id');
    }
}
