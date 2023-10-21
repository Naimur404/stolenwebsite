<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateShipmentCodStatusRequest extends Request
{
    public function rules(): array
    {
        return [
            'status' => 'required|' . Rule::in(ShippingCodStatusEnum::values()),
        ];
    }
}
