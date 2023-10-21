<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Enums\GlobalOptionEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ProductRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:220',
            'price' => 'numeric|nullable|min:0|max:100000000000',
            'sale_price' => 'numeric|nullable|min:0|max:100000000000',
            'start_date' => 'date|nullable|required_if:sale_type,1',
            'end_date' => 'date|nullable|after:' . ($this->input('start_date') ?? Carbon::now()->toDateTimeString()),
            'wide' => 'numeric|nullable|min:0|max:100000000',
            'height' => 'numeric|nullable|min:0|max:100000000',
            'weight' => 'numeric|nullable|min:0|max:100000000',
            'length' => 'numeric|nullable|min:0|max:100000000',
            'images' => 'sometimes|array',
            'status' => Rule::in(BaseStatusEnum::values()),
            'quantity' => 'numeric|nullable|min:0|max:100000000',
            'product_type' => Rule::in(ProductTypeEnum::values()),
            'product_files_input' => 'nullable|array',
            'product_files_input.*' => 'nullable|file|mimes:' . config('plugins.ecommerce.general.digital_products.allowed_mime_types'),
            'product_files_external' => 'nullable|array',
            'product_files_external.*.name' => 'nullable|string|max:120',
            'product_files_external.*.link' => 'required|url|max:400',
            'product_files_external.*.size' => 'nullable|numeric|min:0|max:100000000',
            'taxes' => 'nullable|array',
            'cost_per_item' => 'numeric|nullable|min:0|max:100000000000',
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('ec_products')->ignore($this->route('product')),
            ],
            'general_license_code' => 'nullable|in:0,1',
        ];

        if (EcommerceHelper::isEnabledProductOptions()) {
            $options = $this->input('options');

            if (! empty($options)) {
                $productOptionRules = $this->getRuleProductOptionRequest($options);
                $rules = array_merge($rules, $productOptionRules);
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => trans('plugins/ecommerce::products.product_create_validate_name_required'),
            'sale_price.max' => trans('plugins/ecommerce::products.product_create_validate_sale_price_max'),
            'sale_price.required_if' => trans('plugins/ecommerce::products.product_create_validate_sale_price_required_if'),
            'end_date.after' => trans('plugins/ecommerce::products.product_create_validate_end_date_after'),
            'start_date.required_if' => trans('plugins/ecommerce::products.product_create_validate_start_date_required_if'),
            'sale_price' => trans('plugins/ecommerce::products.product_create_validate_sale_price'),
        ];
    }

    public function attributes(): array
    {
        $options = $this->input('options');
        $attrs = [
            'product_files_external.*.link' => trans('plugins/ecommerce::products.digital_attachments.external_link_download'),
        ];
        if (! empty($options)) {
            foreach ($options as $key => $option) {
                $name = sprintf('options.%s.name', $key);
                $type = sprintf('options.%s.option_type', $key);
                $value = sprintf('options.%s.values', $key);
                $optionNumber = intval($key) + 1;
                $attrs[$name] = trans('plugins/ecommerce::product-option.option_name_attribute', ['key' => $optionNumber]);
                $attrs[$type] = trans('plugins/ecommerce::product-option.option_type_attribute', ['key' => $optionNumber]);
                $attrs[$value] = trans('plugins/ecommerce::product-option.option_value_name_attribute', ['key' => $optionNumber]);
                if (! empty($option['values'])) {
                    $attrs = array_merge($attrs, $this->getAttributeValue($key, $option['values']));
                }
            }
        }

        return $attrs;
    }

    protected function getAttributeValue(string $optionKey, array $values = []): array
    {
        $attrs = [];
        foreach ($values as $key => $value) {
            foreach ($value as $valueKey => $item) {
                $attrs['options.' . $optionKey . '.values.' . $key . '.' . $valueKey] = trans('plugins/ecommerce::product-option.option_value_attribute', ['option_key' => $optionKey, 'value_key' => $valueKey, 'item' => $item]);
            }
        }

        return $attrs;
    }

    protected function getRuleProductOptionRequest(array $options = []): array
    {
        $rules = [];
        foreach ($options as $key => $option) {
            $name = sprintf('options.%s.name', $key);
            $type = sprintf('options.%s.option_type', $key);
            $value = sprintf('options.%s.values', $key);
            $rules[$name] = 'required';
            $rules[$type] = 'required';
            $rules[$value] = 'required';
            $optionRules = [];

            if (isset($option['values'])) {
                $optionRules = $this->getRulesOfProductOptionValues(sprintf('options.%s', $key), $option['option_type'], array_filter($option['values']));
            }

            $rules = array_merge($rules, $optionRules);
        }

        return $rules;
    }

    protected function getRulesOfProductOptionValues(string $baseName, string $optionType, array $values = []): array
    {
        $rules = [];

        foreach ($values as $key => $value) {
            $rules[$baseName . '.values.' . $key . '.affect_price'] = 'numeric|min:0';
            if (isset($value['affect_type']) && $value['affect_type'] == GlobalOptionEnum::TYPE_PERCENT) {
                $rules[$baseName . '.values.' . $key . '.affect_price'] = 'numeric|between:1,100';
            }

            if ($optionType != GlobalOptionEnum::FIELD) {
                $rules[$baseName . '.values.' . $key . '.option_value'] = 'required';
            }
        }

        return $rules;
    }
}
