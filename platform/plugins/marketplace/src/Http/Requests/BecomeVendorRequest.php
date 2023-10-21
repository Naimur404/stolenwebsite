<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class BecomeVendorRequest extends Request
{
    public function rules(): array
    {
        return [
            'shop_name' => 'required|min:2',
            'shop_phone' => 'required|' . BaseHelper::getPhoneValidationRule(),
            'shop_url' => 'required|max:200',
        ];
    }
}
