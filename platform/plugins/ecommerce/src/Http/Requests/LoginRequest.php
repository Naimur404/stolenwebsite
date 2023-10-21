<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        return apply_filters('ecommerce_customer_login_form_validation_rules', $rules);
    }

    public function attributes(): array
    {
        return apply_filters('ecommerce_customer_login_form_validation_attributes', [
            'email' => __('Email'),
            'password' => __('Password'),
        ]);
    }

    public function messages(): array
    {
        return apply_filters('ecommerce_customer_login_form_validation_messages', []);
    }
}
