<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Traits\LocationTrait;

class StoreLocator extends BaseModel
{
    use LocationTrait;

    protected $table = 'ec_store_locators';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'country',
        'state',
        'city',
        'is_primary',
        'is_shipping_location',
    ];
}
