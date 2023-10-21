<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Marketplace\Enums\PayoutPaymentMethodsEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreRequest extends Request
{
    public function rules(): array
    {
        return array_merge([
            'name' => 'required',
            'customer_id' => 'required',
            'description' => 'max:400',
            'status' => Rule::in(BaseStatusEnum::values()),
            'company' => 'max:255',
            'zip_code' => 'nullable|max:20',
        ], PayoutPaymentMethodsEnum::getRules('bank_info'));
    }

    public function attributes(): array
    {
        return array_merge([
            'bank_info' => __('Payout info'),
        ], PayoutPaymentMethodsEnum::getAttributes('bank_info'));
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $channel = $this->input('payout_payment_method');

        $this->merge(['bank_info' => Arr::get($this->input('bank_info'), $channel)]);
    }
}
