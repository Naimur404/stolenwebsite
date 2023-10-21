<?php

namespace Botble\Shippo\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Shipment;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Shippo\Shippo;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter('handle_shipping_fee', [$this, 'handleShippingFee'], 11, 2);

        add_filter(SHIPPING_METHODS_SETTINGS_PAGE, [$this, 'addSettings'], 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == ShippingMethodEnum::class) {
                $values['SHIPPO'] = SHIPPO_SHIPPING_METHOD_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == ShippingMethodEnum::class && $value == SHIPPO_SHIPPING_METHOD_NAME) {
                return 'Shippo';
            }

            return $value;
        }, 2, 2);

        add_filter('shipment_buttons_detail_order', function (?string $content, Shipment $shipment) {
            Assets::addScriptsDirectly('vendor/core/plugins/shippo/js/shippo.js');

            return $content . view('plugins/shippo::buttons', compact('shipment'))->render();
        }, 1, 2);
    }

    public function handleShippingFee(array $result, array $data): array
    {
        if (! $this->app->runningInConsole() && setting('shipping_shippo_status') == 1) {
            Arr::forget($data, 'extra.COD');
            $results = app(Shippo::class)->getRates($data);
            if (Arr::get($data, 'payment_method') == PaymentMethodEnum::COD) {
                $rates = Arr::get($results, 'shipment.rates') ?: [];
                foreach ($rates as &$rate) {
                    $rate['disabled'] = true;
                    $rate['error_message'] = __('Not available in COD payment option.');
                }

                Arr::set($results, 'shipment.rates', $rates);
            }

            $result['shippo'] = Arr::get($results, 'shipment.rates') ?: [];
        }

        return $result;
    }

    public function addSettings(string|null $settings): string
    {
        $logFiles = [];

        if (setting('shipping_shippo_logging')) {
            foreach (BaseHelper::scanFolder(storage_path('logs')) as $file) {
                if (Str::startsWith($file, 'shippo-')) {
                    $logFiles[] = $file;
                }
            }
        }

        return $settings . view('plugins/shippo::settings', compact('logFiles'))->render();
    }
}
