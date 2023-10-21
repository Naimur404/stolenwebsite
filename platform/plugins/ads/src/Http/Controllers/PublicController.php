<?php

namespace Botble\Ads\Http\Controllers;

use Botble\Ads\Models\Ads;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;

class PublicController extends BaseController
{
    public function getAdsClick(string $key, BaseHttpResponse $response)
    {
        $ads = Ads::query()->where('key', $key)->first();

        if (! $ads || ! $ads->url) {
            return $response->setNextUrl(route('public.single'));
        }

        $ads->clicked++;
        $ads->save();

        return $response->setNextUrl($ads->url);
    }
}
