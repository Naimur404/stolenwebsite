<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CreateProductWhenCreatingOrderRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'price' => 'numeric|nullable',
        ];
    }
}
