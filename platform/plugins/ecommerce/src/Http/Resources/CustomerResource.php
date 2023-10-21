<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Models\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar_url,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'description' => $this->description,
        ];
    }
}
