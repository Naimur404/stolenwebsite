<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\OrderReturnReasonEnum;
use Botble\Ecommerce\Enums\OrderReturnStatusEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends BaseModel
{
    protected $table = 'ec_order_returns';

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'order_status',
        'return_status',
        'store_id',
    ];

    protected $casts = [
        'order_status' => OrderStatusEnum::class,
        'return_status' => OrderReturnStatusEnum::class,
        'reason' => OrderReturnReasonEnum::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id')->withDefault();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id')->withDefault();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class, 'order_return_id');
    }

    protected static function booted(): void
    {
        self::deleting(function (OrderReturn $orderReturn) {
            $orderReturn->items()->delete();
        });

        static::creating(function (OrderReturn $orderReturn) {
            $orderReturn->code = static::generateUniqueCode();
        });
    }

    public static function generateUniqueCode(): string
    {
        $nextInsertId = static::query()->max('id') + 1;

        do {
            $code = get_order_code($nextInsertId);
            $nextInsertId++;
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }
}
