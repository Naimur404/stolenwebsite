<?php

namespace Botble\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'predefined_range' => ['nullable', 'string'],
            'changed_predefined_range' => ['nullable', 'boolean'],
        ];
    }
}
