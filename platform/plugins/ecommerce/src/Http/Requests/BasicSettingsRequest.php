<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Facades\Currency;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class BasicSettingsRequest extends Request
{
    public function prepareForValidation(): void
    {
        $this->merge([
            'currencies_data' => json_decode($this->input('currencies'), true),
        ]);
    }

    public function rules(): array
    {
        return [
            'store_name' => 'required|string|max:120',
            'store_company' => 'nullable|string|max:120',
            'store_phone' => 'required|' . BaseHelper::getPhoneValidationRule(),
            'store_email' => 'nullable|email',
            'store_address' => 'required|string|max:255',
            'store_country' => 'nullable|string|max:120',
            'store_state' => 'nullable|string|max:120',
            'store_city' => 'nullable|string|max:120',
            'store_vat_number' => 'nullable|string|max:120',
            'store_order_prefix' => 'nullable|string|max:120',
            'store_order_suffix' => 'nullable|string|max:120',
            'store_weight_unit' => 'required|string|in:g,kg,lb,oz',
            'store_width_height_unit' => 'required|string|in:cm,m,inch',
            'currencies_data.*.title' => 'required|string|' . Rule::in(Currency::currencyCodes()),
            'currencies_data.*.symbol' => 'required|string',
            'enable_auto_detect_visitor_currency' => 'nullable|in:0,1',
            'add_space_between_price_and_currency' => 'nullable|in:0,1',
            'thousands_separator' => 'nullable|string',
            'use_exchange_rate_from_api' => 'nullable|in:0,1',
            'exchange_rate_api_provider' => 'nullable|in:api_layer,open_exchange_rate',
            'api_layer_api_key' => 'nullable|required_if:exchange_rate_api_provider,api_layer|string',
            'open_exchange_app_id' => 'nullable|required_if:exchange_rate_api_provider,open_exchange_rate|string',
        ];
    }

    public function messages(): array
    {
        return [
            'currencies_data.*.title.in' => trans('plugins/ecommerce::currency.invalid_currency_name', [
                'currencies' => implode(', ', Currency::currencyCodes()),
            ]),
        ];
    }

    public function attributes(): array
    {
        return [
            'currencies_data.*.title' => trans('plugins/ecommerce::currency.invalid_currency_name'),
            'currencies_data.*.symbol' => trans('plugins/ecommerce::currency.symbol'),
        ];
    }
}
