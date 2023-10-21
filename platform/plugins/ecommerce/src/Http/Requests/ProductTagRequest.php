<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductTagRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'description' => 'nullable|string|max:400',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
