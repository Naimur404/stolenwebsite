<?php

namespace Botble\Shortcode\Http\Requests;

use Botble\Support\Http\Requests\Request;

class GetShortcodeDataRequest extends Request
{
    public function rules(): array
    {
        return [
            'key' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:10000',
        ];
    }
}
