<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShipmentHistory extends BaseModel
{
    protected $table = 'ec_shipment_histories';

    protected $fillable = [
        'action',
        'description',
        'user_id',
        'shipment_id',
        'order_id',
        'user_type',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo()->withDefault();
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id')->withDefault();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id')->withDefault();
    }
}
