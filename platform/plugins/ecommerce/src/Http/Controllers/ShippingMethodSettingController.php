<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Setting\Supports\SettingStore;
use Botble\Support\Services\Cache\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ShippingMethodSettingController extends BaseController
{
    public function update(Request $request, BaseHttpResponse $response, SettingStore $settingStore)
    {
        $data = Arr::where($request->except(['_token']), function ($value, $key) {
            return Str::startsWith($key, 'shipping_');
        });
        foreach ($data as $settingKey => $settingValue) {
            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();

        $cache = new Cache(app('cache'), HandleShippingFeeService::class);
        $cache->flush();

        return $response->setMessage(trans('plugins/ecommerce::shipping.saved_shipping_settings_success'));
    }
}
