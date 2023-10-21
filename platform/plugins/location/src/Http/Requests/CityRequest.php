<?php

namespace Botble\Location\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CityRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'state_id' => 'required|integer',
            'country_id' => 'required|integer',
            'slug' => [
                'nullable',
                'string',
                Rule::unique('cities', 'slug')->ignore($this->route('city')),
            ],
            'image' => 'nullable|string',
            'order' => 'required|integer|min:0|max:127',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
