<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Traits\LocationTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRuleItem extends BaseModel
{
    use LocationTrait;

    protected $table = 'ec_shipping_rule_items';

    protected $fillable = [
        'shipping_rule_id',
        'country',
        'state',
        'city',
        'adjustment_price',
        'is_enabled',
        'zip_code',
    ];

    public function setAdjustmentPriceAttribute(string|null $value)
    {
        $this->attributes['adjustment_price'] = (float)str_replace(',', '', $value);
    }

    public function shippingRule(): BelongsTo
    {
        return $this->belongsTo(ShippingRule::class)->withDefault();
    }

    public function getNameItemAttribute(): string
    {
        return sprintf(' "%s, %s, %s"', $this->state_name, $this->city_name, $this->zip_code);
    }
}
