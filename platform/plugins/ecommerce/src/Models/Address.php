<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Traits\LocationTrait;

class Address extends BaseModel
{
    use LocationTrait;

    protected $table = 'ec_customer_addresses';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'country',
        'state',
        'city',
        'address',
        'zip_code',
        'customer_id',
        'is_default',
    ];

    public function getFullAddressAttribute(): string
    {
        return ($this->address ? ($this->address . ', ') : null) .
            ($this->city_name ? ($this->city_name . ', ') : null) .
            ($this->state_name ? ($this->state_name . ', ') : null) .
            (EcommerceHelper::isUsingInMultipleCountries() ? ($this->country_name ?: null) : '') .
            (EcommerceHelper::isZipCodeEnabled() && $this->zip_code ? ', ' . $this->zip_code : '');
    }
}
