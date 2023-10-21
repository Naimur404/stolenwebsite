<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\OrderReturnStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateOrderReturnRequest extends Request
{
    public function rules(): array
    {
        return [
            'return_status' => 'required|string|' . Rule::in(OrderReturnStatusEnum::values()),
        ];
    }
}
