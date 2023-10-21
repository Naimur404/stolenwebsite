<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AddShippingRegionRequest extends Request
{
    public function rules(): array
    {
        return [
            'region' => ['sometimes', 'string', Rule::in(array_keys(EcommerceHelper::getAvailableCountries()))],
        ];
    }
}
