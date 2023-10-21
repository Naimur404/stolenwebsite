<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CustomerCreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120|min:2',
            'email' => 'required|max:60|min:6|email|unique:ec_customers',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ];
    }
}
