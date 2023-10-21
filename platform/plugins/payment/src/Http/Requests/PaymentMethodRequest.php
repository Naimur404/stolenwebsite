<?php

namespace Botble\Payment\Http\Requests;

use Botble\Support\Http\Requests\Request;

class PaymentMethodRequest extends Request
{
    public function rules(): array
    {
        return [
            'type' => 'required|string|max:120',
        ];
    }
}
