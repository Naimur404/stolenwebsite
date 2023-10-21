<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Marketplace\Enums\PayoutPaymentMethodsEnum;
use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WithdrawalRequest extends Request
{
    public function rules(): array
    {
        return [
            'images' => 'nullable|array',
            'status' => Rule::in(WithdrawalStatusEnum::values()),
            'description' => 'nullable|max:400',
            'payment_channel' => Rule::in(array_keys(PayoutPaymentMethodsEnum::payoutMethodsEnabled())),
        ];
    }
}
