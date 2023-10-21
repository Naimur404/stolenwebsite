<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ShippingRuleItemRequest extends Request
{
    public function rules(): array
    {
        return [
            'shipping_rule_id' => [
                'required',
                Rule::exists(ShippingRule::class, 'id')->where(function ($query) {
                    return $query->whereIn('type', ShippingRuleTypeEnum::keysAllowRuleItems());
                }),
            ],
            'country' => 'required',
            'state' => [
                Rule::requiredIf(function () {
                    return ShippingRule::query()
                        ->where([
                            'id' => $this->input('shipping_rule_id'),
                            'type' => ShippingRuleTypeEnum::BASED_ON_LOCATION,
                        ])
                        ->count();
                }),
            ],
            'city' => 'nullable',
            'zip_code' => [
                'max:20',
                Rule::requiredIf(function () {
                    return ShippingRule::query()
                        ->where([
                            'id' => $this->input('shipping_rule_id'),
                            'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
                        ])
                        ->count();
                }),
            ],
            'adjustment_price' => 'required|numeric|min:-100000000000|max:100000000000',
            'is_enabled' => Rule::in(['0', '1']),
        ];
    }
}
