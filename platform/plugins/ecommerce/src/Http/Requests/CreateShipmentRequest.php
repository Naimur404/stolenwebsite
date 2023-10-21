<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CreateShipmentRequest extends Request
{
    public function rules(): array
    {
        return [
            'method' => 'required|string',
        ];
    }
}
