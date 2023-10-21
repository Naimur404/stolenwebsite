<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Models\Address;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Address
 */
class CustomerAddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'country_name' => $this->country_name,
            'state_name' => $this->state_name,
            'city_name' => $this->city_name,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'is_default' => $this->is_default,
            'customer_id' => $this->customer_id,
            'full_address' => $this->full_address,
        ];
    }
}
