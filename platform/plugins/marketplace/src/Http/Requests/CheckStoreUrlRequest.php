<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CheckStoreUrlRequest extends Request
{
    public function rules(): array
    {
        return [
            'url' => 'required|max:200',
        ];
    }
}
