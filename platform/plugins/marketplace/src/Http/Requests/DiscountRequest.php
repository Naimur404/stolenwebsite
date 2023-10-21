<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class DiscountRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'code' => 'required|string|max:20|unique:ec_discounts,code',
            'value' => 'required|numeric|min:0',
            'type_option' => 'required|' . Rule::in(array_keys(MarketplaceHelper::discountTypes())),
            'quantity' => 'required_without:is_unlimited|numeric|min:1',
            'start_date' => 'nullable|date|date_format:' . config('core.base.general.date_format.date'),
            'end_date' => 'nullable|date|date_format:' . config('core.base.general.date_format.date') . '|after:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required_if' => trans('plugins/ecommerce::discount.create_discount_validate_title_required_if'),
            'value.required' => trans('plugins/ecommerce::discount.create_discount_validate_value_required'),
            'target.required' => trans('plugins/ecommerce::discount.create_discount_validate_target_required'),
        ];
    }
}
