<?php

namespace Botble\Faq\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class FaqRequest extends Request
{
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
