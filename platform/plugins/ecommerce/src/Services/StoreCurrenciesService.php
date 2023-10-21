<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Models\Currency;

class StoreCurrenciesService
{
    public function execute(array $currencies, array $deletedCurrencies): array
    {
        if ($deletedCurrencies) {
            Currency::query()->whereIn('id', $deletedCurrencies)->delete();
        }

        foreach ($currencies as $item) {
            if (! $item['title'] || ! $item['symbol']) {
                continue;
            }

            $item['title'] = substr(strtoupper($item['title']), 0, 3);
            $item['decimals'] = (int)$item['decimals'];
            $item['decimals'] = $item['decimals'] < 10 ? $item['decimals'] : 2;

            if (count($currencies) == 1) {
                $item['is_default'] = 1;
            }

            $currency = Currency::query()->find($item['id']);

            if (! $currency) {
                Currency::query()->create($item);
            } else {
                $currency->fill($item);
                $currency->save();
            }
        }

        return [
            'error' => false,
        ];
    }
}
