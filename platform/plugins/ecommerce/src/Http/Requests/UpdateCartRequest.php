<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class UpdateCartRequest extends Request
{
    public function rules(): array
    {
        $rules = [];
        foreach (array_keys($this->input('items', [])) as $rowId) {
            $rules = [
                'items.' . $rowId . '.rowId' => 'required|min:6',
                'items.' . $rowId . '.values' => 'required',
                'items.' . $rowId . '.values.qty' => 'required|integer|min:1',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [];

        foreach (array_keys($this->input('items', [])) as $rowId) {
            $messages = [
                'items.' . $rowId . '.rowId.required' => __('Cart item ID is required!'),
                'items.' . $rowId . '.values.qty.required' => __('Quantity is required!'),
                'items.' . $rowId . '.values.qty.integer' => __('Quantity must be a number!'),
            ];
        }

        return $messages;
    }
}
