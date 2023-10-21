<?php

namespace Botble\PayPal\Http\Requests;

use Botble\Support\Http\Requests\Request;

class PayPalPaymentCallbackRequest extends Request
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'currency' => 'required',
        ];
    }
}
