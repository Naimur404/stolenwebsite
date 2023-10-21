<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ProductUpdateOrderByRequest extends Request
{
    public function rules(): array
    {
        return [
            'pk' => 'required|integer|min:1',
            'value' => 'required|numeric|min:0|max:100000',
        ];
    }
}
