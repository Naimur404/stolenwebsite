<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;

class CreateAddressFromAdminRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'is_default' => 'integer|min:0|max:1',
            'customer_id' => 'required',
        ];

        if (! EcommerceHelper::isUsingInMultipleCountries()) {
            $this->merge(['country' => EcommerceHelper::getFirstCountryId()]);
        }

        return array_merge($rules, EcommerceHelper::getCustomerAddressValidationRules());
    }
}
