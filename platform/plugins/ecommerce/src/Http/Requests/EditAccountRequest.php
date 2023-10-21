<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class EditAccountRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'sometimes|' . BaseHelper::getPhoneValidationRule(),
            'dob' => 'date|max:20|sometimes',
        ];
    }
}
