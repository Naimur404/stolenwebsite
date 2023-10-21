<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class FlashSaleRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'end_date' => 'required|date',
            'products_extra.*.price' => 'required|numeric',
            'products_extra.*.quantity' => 'required|numeric',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }

    public function attributes(): array
    {
        return [
            'products_extra.*.price' => trans('plugins/ecommerce::products.price'),
            'products_extra.*.quantity' => trans('plugins/ecommerce::products.quantity'),
        ];
    }
}
