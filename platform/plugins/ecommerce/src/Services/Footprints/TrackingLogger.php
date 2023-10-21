<?php

namespace Botble\Ecommerce\Services\Footprints;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class TrackingLogger implements TrackingLoggerInterface
{
    protected ?Request $request = null;

    public function track(Request $request): Request
    {
        $this->request = $request;

        $data = $this->captureAttributionData();

        if ($data && ! app(FootprinterInterface::class)->getFootprints()) {
            Cookie::queue(
                'botble_footprints_cookie_data',
                json_encode($data),
                604800,
                null,
                config('session.domain')
            );
        }

        return $this->request;
    }

    protected function captureAttributionData(): array
    {
        $attributes = array_merge(
            [
                'footprint' => $this->request->footprint(),
                'ip' => $this->captureIp(),
                'landing_domain' => $this->captureLandingDomain(),
                'landing_page' => $this->captureLandingPage(),
                'landing_params' => $this->captureLandingParams(),
                'referral' => $this->captureReferral(),
                'gclid' => $this->captureGCLID(),
                'fclid' => $this->captureFCLID(),
            ],
            $this->captureUTM(),
            $this->captureReferrer(),
            $this->getCustomParameter()
        );

        return array_map(function (string|null $item) {
            return is_string($item) ? substr($item, 0, 255) : $item;
        }, $attributes);
    }

    protected function getCustomParameter(): array
    {
        return [];
    }

    protected function captureIp(): string|null
    {
        return $this->request->ip();
    }

    protected function captureLandingDomain(): string
    {
        return $this->request->server('SERVER_NAME');
    }

    protected function captureLandingPage(): string
    {
        return $this->request->path();
    }

    protected function captureLandingParams(): string|null
    {
        return $this->request->getQueryString();
    }

    protected function captureUTM(): array
    {
        $parameters = ['utm_source', 'utm_campaign', 'utm_medium', 'utm_term', 'utm_content'];

        $utm = [];

        foreach ($parameters as $parameter) {
            if ($this->request->has($parameter)) {
                $utm[$parameter] = $this->request->input($parameter);
            } else {
                $utm[$parameter] = null;
            }
        }

        return $utm;
    }

    protected function captureReferrer(): array
    {
        $referrer = [];

        $referrer['referrer_url'] = $this->request->headers->get('referer');

        $parsedUrl = parse_url($referrer['referrer_url']);

        $referrer['referrer_domain'] = $parsedUrl['host'] ?? null;

        return $referrer;
    }

    protected function captureGCLID(): string|null
    {
        return $this->request->input('gclid');
    }

    protected function captureFCLID(): string|null
    {
        return $this->request->input('fbclid');
    }

    protected function captureReferral(): string|null
    {
        return $this->request->input('ref');
    }
}
