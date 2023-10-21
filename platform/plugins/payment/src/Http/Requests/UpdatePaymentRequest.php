<?php

namespace Botble\Payment\Http\Requests;

use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends Request
{
    public function rules(): array
    {
        return [
            'status' => Rule::in(PaymentStatusEnum::values()),
        ];
    }
}
