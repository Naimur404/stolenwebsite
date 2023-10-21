<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\OrderReturnReasonEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturnItem extends BaseModel
{
    protected $table = 'ec_order_return_items';

    protected $fillable = [
        'order_return_id',
        'order_product_id',
        'product_id',
        'product_name',
        'product_image',
        'qty',
        'price',
        'reason',
        'refund_amount',
    ];

    protected $casts = [
        'reason' => OrderReturnReasonEnum::class,
    ];

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class, 'order_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
