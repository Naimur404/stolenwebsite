<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SearchProductAndVariationsRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_ids' => 'sometimes|array',
            'keyword' => 'nullable|string|max:220',
        ];
    }
}
