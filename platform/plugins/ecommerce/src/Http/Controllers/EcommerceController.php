<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\AdvancedSettingsRequest;
use Botble\Ecommerce\Http\Requests\BasicSettingsRequest;
use Botble\Ecommerce\Http\Requests\StoreLocatorRequest;
use Botble\Ecommerce\Http\Requests\TrackingSettingsRequest;
use Botble\Ecommerce\Http\Requests\UpdatePrimaryStoreRequest;
use Botble\Ecommerce\Models\Currency;
use Botble\Ecommerce\Models\StoreLocator;
use Botble\Ecommerce\Services\ExchangeRates\ExchangeRateInterface;
use Botble\Ecommerce\Services\StoreCurrenciesService;
use Botble\JsValidation\Facades\JsValidator;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Facades\Cache;
use Throwable;

class EcommerceController extends BaseController
{
    public function getSettings()
    {
        PageTitle::setTitle(trans('plugins/ecommerce::ecommerce.basic_settings'));

        Assets::addScripts(['jquery-ui'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/currencies.js',
                'vendor/core/plugins/ecommerce/js/setting.js',
                'vendor/core/plugins/ecommerce/js/store-locator.js',
            ])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/css/ecommerce.css',
                'vendor/core/plugins/ecommerce/css/currencies.css',
            ]);

        Assets::addScriptsDirectly('vendor/core/core/setting/js/setting.js')
            ->addStylesDirectly('vendor/core/core/setting/css/setting.css')
            ->addScripts(['jquery-validation', 'form-validation']);

        $jsValidation = JsValidator::formRequest(BasicSettingsRequest::class);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        $currencies = Currency::query()
            ->orderBy('order')
            ->get()
            ->toArray();

        $storeLocators = StoreLocator::query()->get();

        return view('plugins/ecommerce::settings.index', compact('currencies', 'storeLocators', 'jsValidation'));
    }

    public function getAdvancedSettings()
    {
        PageTitle::setTitle(trans('plugins/ecommerce::ecommerce.advanced_settings'));

        Assets::addScripts(['jquery-ui'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/setting.js',
            ]);

        Assets::addScriptsDirectly('vendor/core/core/setting/js/setting.js')
            ->addStylesDirectly('vendor/core/core/setting/css/setting.css')
            ->addScripts(['jquery-validation', 'form-validation']);

        $jsValidation = JsValidator::formRequest(AdvancedSettingsRequest::class);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        return view('plugins/ecommerce::settings.advanced-settings', compact('jsValidation'));
    }

    public function getTrackingSettings()
    {
        PageTitle::setTitle(trans('plugins/ecommerce::ecommerce.setting.tracking_settings'));

        Assets::addStylesDirectly([
            'vendor/core/plugins/ecommerce/css/ecommerce.css',
            'vendor/core/core/base/libraries/codemirror/lib/codemirror.css',
            'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.css',
            'vendor/core/packages/theme/css/custom-css.css',
        ])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/setting.js',
                'vendor/core/core/base/libraries/codemirror/lib/codemirror.js',
                'vendor/core/core/base/libraries/codemirror/lib/javascript.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/anyword-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/javascript-hint.js',
                'vendor/core/packages/theme/js/custom-js.js',
            ]);

        Assets::addScriptsDirectly('vendor/core/core/setting/js/setting.js')
            ->addStylesDirectly('vendor/core/core/setting/css/setting.css')
            ->addScripts(['jquery-validation', 'form-validation']);

        $jsValidation = JsValidator::formRequest(TrackingSettingsRequest::class);

        return view('plugins/ecommerce::settings.tracking-settings', compact('jsValidation'));
    }

    public function postSettings(
        BasicSettingsRequest $request,
        BaseHttpResponse $response,
        StoreCurrenciesService $service,
        SettingStore $settingStore
    ) {
        foreach ($request->except([
            '_token',
            'currencies',
            'currencies_data',
            'deleted_currencies',
        ]) as $settingKey => $settingValue) {
            $settingStore->set(EcommerceHelper::getSettingPrefix() . $settingKey, $settingValue);
        }

        $settingStore->save();

        $primaryStore = StoreLocator::query()->where(['is_primary' => 1])->first();

        if (! $primaryStore) {
            $primaryStore = new StoreLocator();
            $primaryStore->is_primary = true;
            $primaryStore->is_shipping_location = true;
        }

        $primaryStore->name = $primaryStore->name ?? $request->input(
            'store_name',
            trans('plugins/ecommerce::store-locator.default_store')
        );
        $primaryStore->phone = $request->input('store_phone');
        $primaryStore->email = $primaryStore->email ?? get_admin_email()->first();
        $primaryStore->address = $request->input('store_address');
        $primaryStore->country = $request->input('store_country');
        $primaryStore->state = $request->input('store_state');
        $primaryStore->city = $request->input('store_city');
        $primaryStore->save();

        $currencies = json_decode($request->input('currencies'), true) ?: [];

        if (! $currencies) {
            return $response
                ->setNextUrl(route('ecommerce.settings'))
                ->setError()
                ->setMessage(trans('plugins/ecommerce::currency.require_at_least_one_currency'));
        }

        $deletedCurrencies = json_decode($request->input('deleted_currencies', []), true) ?: [];

        $response->setNextUrl(route('ecommerce.settings'));

        $storedCurrencies = $service->execute($currencies, $deletedCurrencies);

        if ($storedCurrencies['error']) {
            return $response
                ->setError()
                ->setMessage($storedCurrencies['message']);
        }

        return $response
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function postAdvancedSettings(
        AdvancedSettingsRequest $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        foreach ($request->except([
            '_token',
        ]) as $settingKey => $settingValue) {
            $settingStore->set(
                EcommerceHelper::getSettingPrefix() . $settingKey,
                is_array($settingValue) ? json_encode(array_values(array_filter($settingValue))) : $settingValue
            );
        }

        $settingStore->save();

        return $response
            ->setNextUrl(route('ecommerce.advanced-settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function postTrackingSettings(
        TrackingSettingsRequest $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        foreach ($request->except([
            '_token',
        ]) as $settingKey => $settingValue) {
            $settingStore->set(EcommerceHelper::getSettingPrefix() . $settingKey, $settingValue);
        }

        $settingStore->save();

        return $response
            ->setNextUrl(route('ecommerce.tracking-settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getStoreLocatorForm(BaseHttpResponse $response, int|string|null $id = null)
    {
        $locator = null;
        if ($id) {
            $locator = StoreLocator::query()->findOrFail($id);
        }

        return $response->setData(view('plugins/ecommerce::settings.store-locator-item', compact('locator'))->render());
    }

    public function postUpdateStoreLocator(
        int|string $id,
        StoreLocatorRequest $request,
        BaseHttpResponse $response,
        SettingStore $settingStore
    ) {
        $request->merge([
            'is_shipping_location' => $request->has('is_shipping_location'),
        ]);

        $locator = StoreLocator::query()->firstOrCreate($request->input(), compact('id'));

        if ($locator->is_primary) {
            $prefix = EcommerceHelper::getSettingPrefix();

            $settingStore
                ->set([
                    $prefix . 'store_phone' => $locator->phone,
                    $prefix . 'store_address' => $locator->address,
                    $prefix . 'store_country' => $locator->country,
                    $prefix . 'store_state' => $locator->state,
                    $prefix . 'store_city' => $locator->city,
                ])
                ->save();
        }

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function postCreateStoreLocator(StoreLocatorRequest $request, BaseHttpResponse $response)
    {
        $request->merge([
            'is_primary' => false,
            'is_shipping_location' => $request->has('is_shipping_location'),
        ]);

        StoreLocator::query()->create($request->input());

        return $response->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function postDeleteStoreLocator(int|string $id, BaseHttpResponse $response)
    {
        $storeLocator = StoreLocator::query()->findOrFail($id);

        $storeLocator->delete();

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function postUpdatePrimaryStore(UpdatePrimaryStoreRequest $request, BaseHttpResponse $response)
    {
        $storeLocator = StoreLocator::query()->findOrFail($request->input('primary_store_id'));

        StoreLocator::query()->where('id', '!=', $storeLocator->getKey())->update(['is_primary' => false]);

        $storeLocator->is_primary = true;

        $storeLocator->save();

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function ajaxGetCountries(BaseHttpResponse $response)
    {
        return $response->setData(EcommerceHelper::getAvailableCountries());
    }

    public function updateCurrenciesFromExchangeApi(
        ExchangeRateInterface $exchangeRateService,
        BaseHttpResponse $response
    ) {
        try {
            $currencyUpdated = $exchangeRateService->getCurrentExchangeRate();

            return $response
                ->setData($currencyUpdated)
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (Throwable $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function clearCacheCurrencyRates(ExchangeRateInterface $exchangeRateService, BaseHttpResponse $response)
    {
        Cache::forget('currency_exchange_rate');

        $exchangeRateService->cacheExchangeRates();

        return $response
            ->setMessage(trans('plugins/ecommerce::currency.clear_cache_rates_successfully'));
    }
}
