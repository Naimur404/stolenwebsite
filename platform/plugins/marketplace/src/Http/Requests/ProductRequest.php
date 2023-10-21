<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Ecommerce\Http\Requests\ProductRequest as BaseProductRequest;

class ProductRequest extends BaseProductRequest
{
    public function rules(): array
    {
        return parent::rules() + ['image_input' => 'image|mimes:jpg,jpeg,png'];
    }
}
