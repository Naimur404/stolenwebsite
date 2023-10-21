<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\GlobalOptionEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductOptionRequest extends Request
{
    public function rules(): array
    {
        return [
            'option_name' => 'required|string',
            'option_type' => [
                Rule::requiredIf(fn () => $this->input('option_type') == GlobalOptionEnum::NA),
            ],
        ];
    }
}
