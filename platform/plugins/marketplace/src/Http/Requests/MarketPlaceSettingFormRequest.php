<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Ecommerce\Http\Requests\ProductRequest as BaseProductRequest;
use Botble\Marketplace\Enums\PayoutPaymentMethodsEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;

class MarketPlaceSettingFormRequest extends BaseProductRequest
{
    protected function prepareForValidation(): void
    {
        if (! array_filter($this->input(MarketplaceHelper::getSettingKey('payout_methods')))) {
            $this->merge([
                MarketplaceHelper::getSettingKey('payout_methods') => [],
            ]);
        }
    }

    public function rules(): array
    {
        $rules = [
            MarketplaceHelper::getSettingKey('payout_methods') => 'required|array:' . implode(',', PayoutPaymentMethodsEnum::values()),
            MarketplaceHelper::getSettingKey('payout_methods') . '.*' => 'in:0,1',
            'marketplace_enable_commission_fee_for_each_category' => 'required|in:0,1',
            'marketplace_check_valid_signature' => 'required|in:0,1',
            'marketplace_verify_vendor' => 'required|in:0,1',
            'marketplace_enable_product_approval' => 'required|in:0,1',
            'marketplace_hide_store_phone_number' => 'required|in:0,1',
            'marketplace_hide_store_email' => 'required|in:0,1',
            'marketplace_allow_vendor_manage_shipping' => 'required|in:0,1',
            'marketplace_fee_per_order' => 'required|min:0|max:100|numeric',
            'marketplace_fee_withdrawal' => 'required|min:0|numeric',
            'max_filesize_upload_by_vendor' => 'required|min:1|numeric',
            'max_product_images_upload_by_vendor' => 'required|min:1|numeric',
        ];

        if ($this->input('marketplace_enable_commission_fee_for_each_category')) {
            // validate request setting category commission
            $commissionByCategory = $this->input('commission_by_category');
            foreach ($commissionByCategory as $key => $item) {
                $commissionFeeName = sprintf('%s.%s.commission_fee', 'commission_by_category', $key);
                $categoryName = sprintf('%s.%s.categories', 'commission_by_category', $key);
                $rules[$commissionFeeName] = 'required|numeric|min:1,max:100';
                $rules[$categoryName] = 'required';
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        if ($this->input('marketplace_enable_commission_fee_for_each_category') == 1) {
            // validate request setting category commission
            $commissionByCategory = $this->input('commission_by_category');
            foreach ($commissionByCategory as $key => $item) {
                $commissionFeeName = sprintf('%s.%s.commission_fee', 'commission_by_category', $key);
                $categoryName = sprintf('%s.%s.categories', 'commission_by_category', $key);
                $attributes[$commissionFeeName] = trans('plugins/marketplace::marketplace.settings.commission_fee_each_category_fee_name', ['key' => $key]);
                $attributes[$categoryName] = trans('plugins/marketplace::marketplace.settings.commission_fee_each_category_name', ['key' => $key]);
            }
        }

        return $attributes;
    }
}
