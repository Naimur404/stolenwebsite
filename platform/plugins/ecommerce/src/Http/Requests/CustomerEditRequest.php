<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CustomerEditRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|max:120|min:2',
            'email' => 'required|max:60|min:6|email|unique:ec_customers,email,' . $this->route('customer'),
        ];

        if ($this->boolean('is_change_password')) {
            $rules['password'] = 'required|string|min:6';
            $rules['password_confirmation'] = 'required|same:password';
        }

        return $rules;
    }
}
