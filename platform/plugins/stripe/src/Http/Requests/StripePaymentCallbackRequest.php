<?php

namespace Botble\Stripe\Http\Requests;

use Botble\Support\Http\Requests\Request;

class StripePaymentCallbackRequest extends Request
{
    public function rules(): array
    {
        return [
            'session_id' => 'required|size:66',
        ];
    }
}
