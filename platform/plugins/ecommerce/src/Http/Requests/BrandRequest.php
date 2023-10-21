<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class BrandRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'slug' => 'nullable|string|max:220',
            'description' => 'nullable|string|max:400',
            'order' => 'required|integer|min:0|max:127',
            'website' => 'nullable|string',
            'status' => Rule::in(BaseStatusEnum::values()),
            'categories' => 'nullable|array',
        ];
    }

    public function attributes(): array
    {
        return [
            'categories.*' => trans('plugins/ecommerce::products.form.categories'),
        ];
    }
}
