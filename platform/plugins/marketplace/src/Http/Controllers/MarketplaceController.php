<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Helper;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\JsValidation\Facades\JsValidator;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\MarketPlaceSettingFormRequest;
use Botble\Marketplace\Models\Store;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Str;

class MarketplaceController extends BaseController
{
    public function getSettings()
    {
        Assets::addScriptsDirectly('vendor/core/core/setting/js/setting.js')
            ->addStylesDirectly('vendor/core/core/setting/css/setting.css');

        Assets::addStylesDirectly('vendor/core/core/base/libraries/tagify/tagify.css')
            ->addScriptsDirectly([
                'vendor/core/core/base/libraries/tagify/tagify.js',
                'vendor/core/core/base/js/tags.js',
                'vendor/core/plugins/marketplace/js/marketplace-setting.js',
            ])
            ->addScripts(['jquery-validation', 'form-validation']);

        PageTitle::setTitle(trans('plugins/marketplace::marketplace.settings.name'));

        $productCategories = ProductCategory::query()->get();
        $commissionEachCategory = [];

        if (MarketplaceHelper::isCommissionCategoryFeeBasedEnabled()) {
            $commissionEachCategory = Store::getCommissionEachCategory();
        }

        $jsValidation = JsValidator::formRequest(MarketPlaceSettingFormRequest::class);

        return view('plugins/marketplace::settings.index', compact('productCategories', 'commissionEachCategory', 'jsValidation'));
    }

    public function postSettings(
        MarketPlaceSettingFormRequest $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        $settingKey = MarketplaceHelper::getSettingKey();
        $filtered = collect($request->all())->filter(function ($value, $key) use ($settingKey) {
            return Str::startsWith($key, $settingKey);
        });

        if ($request->input('marketplace_enable_commission_fee_for_each_category')) {
            $commissionByCategories = $request->input('commission_by_category');
            Store::handleCommissionEachCategory($commissionByCategories);
        }

        $preVerifyVendor = MarketplaceHelper::getSetting('verify_vendor', 1);

        foreach ($filtered as $key => $settingValue) {
            if ($key == $settingKey . 'fee_per_order') {
                $settingValue = $settingValue < 0 ? 0 : min($settingValue, 100);
            }

            $settingStore->set($key, $settingValue);
        }

        $settingStore->save();

        if ($preVerifyVendor != MarketplaceHelper::getSetting('verify_vendor', 1)) {
            Helper::clearCache();
        }

        return $response
            ->setNextUrl(route('marketplace.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
