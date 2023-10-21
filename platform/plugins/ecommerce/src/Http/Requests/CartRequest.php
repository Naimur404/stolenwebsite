<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CartRequest extends Request
{
    public function rules(): array
    {
        return [
            'id' => 'required|min:1',
            'qty' => 'integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => __('Product ID is required'),
        ];
    }
}
