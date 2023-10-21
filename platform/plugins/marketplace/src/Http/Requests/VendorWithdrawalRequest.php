<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Support\Http\Requests\Request;

class VendorWithdrawalRequest extends Request
{
    public function rules(): array
    {
        $fee = MarketplaceHelper::getSetting('fee_withdrawal', 0);

        return [
            'amount' => 'required|numeric|min:1|max:' . (auth('customer')->user()->balance - $fee),
            'description' => 'nullable|max:400',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => __('The balance is not enough for withdrawal'),
        ];
    }
}
