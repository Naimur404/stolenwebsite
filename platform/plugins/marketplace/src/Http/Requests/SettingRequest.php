<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Marketplace\Enums\PayoutPaymentMethodsEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Support\Arr;

class SettingRequest extends Request
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $channel = $this->input('payout_payment_method');

        $this->merge(['bank_info' => [$channel => Arr::get($this->input('bank_info'), $channel)]]);
    }

    public function rules(): array
    {
        return array_merge([
            'name' => 'required|max:120|min:2',
            'email' => 'required|max:60|min:6|email|unique:ec_customers,email,' . $this->user('customer')->id,
            'phone' => 'required|' . BaseHelper::getPhoneValidationRule(),
            'slug' => 'max:255',
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
