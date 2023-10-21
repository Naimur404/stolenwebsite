<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\SettingRequest;
use Botble\Marketplace\Models\Store;
use Botble\Media\Facades\RvMedia;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Contracts\Config\Repository;

class SettingController
{
    public function __construct(Repository $config)
    {
        Assets::setConfig($config->get('plugins.marketplace.assets', []));
    }

    public function index()
    {
        PageTitle::setTitle(__('Settings'));

        Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');

        $store = auth('customer')->user()->store;

        return MarketplaceHelper::view('dashboard.settings', compact('store'));
    }

    public function saveSettings(SettingRequest $request, BaseHttpResponse $response)
    {
        $store = auth('customer')->user()->store;

        $existing = SlugHelper::getSlug($request->input('slug'), SlugHelper::getPrefix(Store::class));

        if ($existing && $existing->reference_id != $store->id) {
            return $response->setError()->setMessage(__('Shop URL is existing. Please choose another one!'));
        }

        if ($request->hasFile('logo_input')) {
            $result = RvMedia::handleUpload($request->file('logo_input'), 0, $store->upload_folder);
            if (! $result['error']) {
                $file = $result['data'];
                $request->merge(['logo' => $file->url]);
            }
        }

        $store->fill($request->input());
        $store->save();

        $customer = $store->customer;

        if ($customer && $customer->id) {
            $vendorInfo = $customer->vendorInfo;
            $vendorInfo->payout_payment_method = $request->input('payout_payment_method');
            $vendorInfo->bank_info = $request->input('bank_info', []);
            $vendorInfo->tax_info = $request->input('tax_info', []);
            $vendorInfo->save();
        }

        $request->merge(['is_slug_editable' => 1]);

        event(new UpdatedContentEvent(STORE_MODULE_SCREEN_NAME, $request, $store));

        return $response
            ->setNextUrl(route('marketplace.vendor.settings'))
            ->setMessage(__('Update successfully!'));
    }
}
