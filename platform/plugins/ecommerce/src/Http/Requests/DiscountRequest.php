<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Support\Http\Requests\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class DiscountRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'can_use_with_promotion' => $this->boolean('can_use_with_promotion'),
            'quantity' => $this->boolean('is_unlimited') ? null : $this->input('quantity'),
            'start_date' => Carbon::parse("{$this->input('start_date')} {$this->input('start_time')}")->toDateTimeString(),
            'end_date' => $this->has('end_date') && ! $this->has('unlimited_time') ? Carbon::parse("{$this->input('end_date')} {$this->input('end_time')}")->toDateTimeString() : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'required_if:type,promotion', 'max:255'],
            'code' => 'nullable|string|required_if:type,coupon|max:20|unique:ec_discounts,code,' . $this->route('discount'),
            'value' => ['required', 'string', 'numeric', 'min:0'],
            'target' => ['required'],
            'can_use_with_promotion' => ['nullable', 'boolean'],
            'type' => ['required', Rule::in(DiscountTypeEnum::values())],
            'type_option' => ['required', Rule::in(DiscountTypeOptionEnum::values())],
            'quantity' => ['required_without:is_unlimited', 'nullable', 'numeric', 'min:1'],
            'min_order_price' => ['nullable', 'numeric', 'min:0'],
            'product_quantity' => ['nullable', 'numeric', 'min:0'],
            'discount_on' => ['nullable', 'string', 'max:40'],
            'start_date' => ['nullable', 'date', 'date_format:' . config('core.base.general.date_format.date_time')],
            'end_date' => ['nullable', 'date', 'date_format:' . config('core.base.general.date_format.date_time'), 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required_if' => trans('plugins/ecommerce::discount.create_discount_validate_title_required_if'),
            'code.required_if' => trans('plugins/ecommerce::discount.create_discount_validate_code_required_if'),
            'value.required' => trans('plugins/ecommerce::discount.create_discount_validate_value_required'),
            'target.required' => trans('plugins/ecommerce::discount.create_discount_validate_target_required'),
        ];
    }
}
