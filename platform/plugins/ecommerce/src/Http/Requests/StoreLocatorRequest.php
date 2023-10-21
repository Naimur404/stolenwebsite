<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class StoreLocatorRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'email' => 'email|nullable|max:60',
            'phone' => 'required|' . BaseHelper::getPhoneValidationRule(),
            'state' => 'required|max:120',
            'city' => 'required|max:120',
            'address' => 'required|max:120',
        ];
    }
}
