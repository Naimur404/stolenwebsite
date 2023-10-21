<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class UpdateOrderRequest extends Request
{
    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
        ];
    }
}
