<?php

namespace Botble\Ecommerce\Services\ExchangeRates;

use Botble\Ecommerce\Facades\Currency;
use Botble\Ecommerce\Repositories\Interfaces\CurrencyInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenExchangeRatesService implements ExchangeRateInterface
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
            $currency->update(['exchange_rate' => number_format($rates[strtoupper($currency->title)], 8, '.', '')]);
        }

        return app(CurrencyInterface::class)->getAllCurrencies();
    }

    public function cacheExchangeRates(): array
    {
        $defaultCurrency = Currency::getDefaultCurrency();
        $currencies = Currency::currencies()->pluck('title')->all();

        $params = [
            'base' => strtoupper($defaultCurrency->title),
        ];

        return Cache::remember('currency_exchange_rate', 86_400, function () use ($params, $currencies) {
            return array_filter($this->request($params)['rates'], function ($key) use ($currencies) {
                return in_array($key, $currencies);
            }, ARRAY_FILTER_USE_KEY);
        });
    }

    protected function request(array $params): array
    {
        $params = array_merge([
            'app_id' => get_ecommerce_setting('open_exchange_app_id'),
        ], $params);

        $response = Http::baseUrl('https://openexchangerates.org/api')
            ->withoutVerifying()
            ->acceptJson()
            ->get('latest.json?' . http_build_query($params));

        if ($response->failed()) {
            throw new Exception($response->json()['description']);
        }

        return $response->json();
    }
}
