<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\Avatar;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Traits\LocationTrait;
use Botble\Media\Facades\RvMedia;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends BaseModel
{
    use LocationTrait;

    protected $table = 'ec_order_addresses';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'address',
        'zip_code',
        'order_id',
        'type',
    ];

    public $timestamps = false;

    protected $casts = [
        'type' => OrderAddressTypeEnum::class,
    ];

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                try {
                    return (new Avatar())->create($this->name)->toBase64();
                } catch (Exception) {
                    return RvMedia::getDefaultImage();
                }
            }
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withDefault();
    }
}
