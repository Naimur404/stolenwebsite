<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;

class AddCustomerWhenCreateOrderRequest extends Request
{
    public function rules(): array
    {
        if (! EcommerceHelper::isUsingInMultipleCountries()) {
            $this->merge(['country' => EcommerceHelper::getFirstCountryId()]);
        }

        $rules = EcommerceHelper::getCustomerAddressValidationRules();
        $rules['email'] = 'required|max:60|min:6|email|unique:ec_customers';

        return $rules;
    }
}
