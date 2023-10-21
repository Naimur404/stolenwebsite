<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ShippingMethodRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:120',
            'order' => 'required|integer|min:0',
        ];

        foreach (config('plugins.ecommerce.shipping.integration_rules.' . $this->input('method_code'), []) as $key => $rule) {
            $rules[$this->input('method_code') . '.' . $key] = $rule['rule'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];
        foreach (config('plugins.ecommerce.shipping.integration_rules.' . $this->input('method_code'), []) as $key => $rule) {
            $attributes[$this->input('method_code') . '.' . $key] = $rule['name'];
        }

        return $attributes;
    }
}
