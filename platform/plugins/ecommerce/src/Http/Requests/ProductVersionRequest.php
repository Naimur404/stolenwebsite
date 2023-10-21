<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ProductVersionRequest extends Request
{
    public function rules(): array
    {
        return [
            'price' => 'numeric|nullable|min:0',
            'sale_price' => 'numeric|nullable|min:0',
            'start_date' => 'date|nullable|required_if:sale_type,1',
            'end_date' => 'date|nullable|after:' . ($this->input('start_date') ?? Carbon::now()->toDateTimeString()),
            'product_files_input' => 'array',
            'product_files_input.*' => 'nullable|file|mimes:' . config('plugins.ecommerce.general.digital_products.allowed_mime_types'),
            'product_files_external' => 'nullable|array',
            'product_files_external.*.name' => 'nullable|string|max:120',
            'product_files_external.*.link' => 'required|url|max:400',
            'product_files_external.*.size' => 'nullable|numeric|min:0|max:100000000',
            'barcode' => [
                'nullable',
                'max:50',
                'string',
                Rule::unique('ec_products')->ignore($this->input('product_id')),
            ],
            'cost_per_item' => 'nullable|numeric|min:0|max:' . $this->input('price'),
            'attribute_sets' => 'nullable|array',
            'attribute_sets.*' => 'required',
            'general_license_code' => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.max' => trans('plugins/ecommerce::products.product_create_validate_sale_price_max'),
            'sale_price.required_if' => trans('plugins/ecommerce::products.product_create_validate_sale_price_required_if'),
            'end_date.after' => trans('plugins/ecommerce::products.product_create_validate_end_date_after'),
            'start_date.required_if' => trans('plugins/ecommerce::products.product_create_validate_start_date_required_if'),
            'sale_price' => trans('plugins/ecommerce::products.product_create_validate_sale_price'),
            'cost_per_item.max' => trans('plugins/ecommerce::products.product_create_validate_cost_per_item_max'),
        ];
    }

    public function attributes(): array
    {
        return [
            'attribute_sets.*' => trans('plugins/ecommerce::product-attribute-sets.attribute_set'),
            'product_files_external.*.link' => trans('plugins/ecommerce::products.digital_attachments.external_link_download'),
        ];
    }
}
