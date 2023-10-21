<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PublicEcommerceController
{
    public function changeCurrency(Request $request, BaseHttpResponse $response, string|null $title = null)
    {
        if (empty($title)) {
            $title = $request->input('currency');
        }

        if (! $title) {
            return $response;
        }

        $currency = Currency::query()->where('title', $title)->first();

        if ($currency) {
            cms_currency()->setApplicationCurrency($currency);
        }

        $url = URL::previous();

        if (! $url || $url === URL::current()) {
            return $response->setNextUrl(route('public.index'));
        }

        if (Str::contains($url, ['min_price', 'max_price'])) {
            $url = preg_replace('/&min_price=[0-9]+/', '', $url);
            $url = preg_replace('/&max_price=[0-9]+/', '', $url);
        }

        return $response->setNextUrl($url);
    }
}
