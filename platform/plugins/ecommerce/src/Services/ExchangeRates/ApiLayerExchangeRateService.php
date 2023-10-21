<?php

namespace Botble\Ecommerce\Services\ExchangeRates;

use Botble\Ecommerce\Facades\Currency;
use Botble\Ecommerce\Repositories\Interfaces\CurrencyInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiLayerExchangeRateService implements ExchangeRateInterface
{
    public function getCurrentExchangeRate(): Collection
    {
        if (! get_ecommerce_setting('open_exchange_app_id')) {
            throw new Exception(trans('plugins/ecommerce::currency.no_api_key'));
        }

        $rates = $this->cacheExchangeRates();

        $defaultCurrency = Currency::getDefaultCurrency();

        if ($defaultCurrency->exchange_rate != 1) {
            $defaultCurrency->update(['exchange_rate' => 1]);
        }

        $currencies = app(CurrencyInterface::class)
            ->getModel()
            ->where('is_default', 0)
            ->get();

        foreach ($currencies as $currency) {
            $currency->update(['exchange_rate' => number_format($rates[$currency->title], 8, '.', '')]);
        }

        return app(CurrencyInterface::class)->getAllCurrencies();
    }

    public function cacheExchangeRates(): array
    {
        $currencies = Currency::currencies();
        $defaultCurrency = Currency::getDefaultCurrency();

        $params = [
            'symbols' => implode(',', $currencies->pluck('title')->toArray()),
            'base' => 'USD',
        ];

        $rates = Cache::remember('currency_exchange_rate', 86_400, function () use ($params) {
            return $this->request($params)['rates'];
        });

        $rates[$defaultCurrency->title] = 1;

        return $rates;
    }

    protected function request(array $params): array
    {
        $response = Http::baseUrl('https://api.apilayer.com')
            ->withoutVerifying()
            ->withHeaders(['apikey' => get_ecommerce_setting('api_layer_api_key')])
            ->acceptJson()
            ->get('exchangerates_data/latest?' . http_build_query($params));

        if ($response->failed()) {
            throw new Exception($response->reason());
        }

        return $response->json();
    }
}
