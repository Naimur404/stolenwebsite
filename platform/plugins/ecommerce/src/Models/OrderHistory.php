<?php

namespace Botble\Ecommerce\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderHistory extends BaseModel
{
    protected $table = 'ec_order_histories';

    protected $fillable = [
        'action',
        'description',
        'user_id',
        'order_id',
        'extras',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id')->withDefault();
    }

    protected function extras(): Attribute
    {
        return Attribute::make(
            get: function (string|null $value): array {
                return json_decode($value, true) ?: [];
            }
        );
    }
}
