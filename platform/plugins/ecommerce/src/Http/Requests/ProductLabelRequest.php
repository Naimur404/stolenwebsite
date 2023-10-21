<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductLabelRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'color' => 'required|string',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
