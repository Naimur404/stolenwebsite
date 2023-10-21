<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class UpdatePrimaryStoreRequest extends Request
{
    public function rules(): array
    {
        return [
            'primary_store_id' => 'required|numeric',
        ];
    }
}
