<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateShipmentStatusRequest extends Request
{
    public function rules(): array
    {
        return [
            'status' => 'required|' . Rule::in(ShippingStatusEnum::values()),
        ];
    }
}
