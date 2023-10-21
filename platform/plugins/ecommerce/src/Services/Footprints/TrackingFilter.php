<?php

namespace Botble\Ecommerce\Services\Footprints;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingFilter implements TrackingFilterInterface
{
    protected ?Request $request = null;

    /**
     * Determine whether the request should be tracked.
     */
    public function shouldTrack(Request $request): bool
    {
        $this->request = $request;

        //Only track get requests
        if (! $this->request->isMethod('get')) {
            return false;
        }

        if ($this->disableOnAuthentication()) {
            return false;
        }

        if ($this->disableInternalLinks()) {
            return false;
        }

        if ($this->disabledLandingPages($this->captureLandingPage())) {
            return false;
        }

        if ($this->disableRobotsTracking()) {
            return false;
        }

        return true;
    }

    protected function disableOnAuthentication(string $guard = 'web'): bool
    {
        return Auth::guard($guard)->check() || Auth::guard('customer')->check();
    }

    protected function disableInternalLinks(): bool
    {
        if ($referrerDomain = $this->request->headers->get('referer')) {
            $referrerDomain = parse_url($referrerDomain)['host'] ?? null;
            $requestDomain = $this->request->server('SERVER_NAME');

            if ($referrerDomain && $referrerDomain === $requestDomain) {
                return true;
            }
        }

        return false;
    }

    protected function disabledLandingPages(string|null $landingPage = null): bool|array
    {
        $blacklist = [];

        if ($landingPage) {
            $k = in_array($landingPage, $blacklist);

            return ! ($k === false);
        }

        return $blacklist;
    }

    protected function captureLandingPage(): string
    {
        return $this->request->path();
    }

    protected function disableRobotsTracking(): bool
    {
        $ignoredBots = config('core.base.general.error_reporting.ignored_bots', []);
        $agent = strtolower(request()->server('HTTP_USER_AGENT'));

        if (empty($agent)) {
            return false;
        }

        foreach ($ignoredBots as $bot) {
            if ((str_contains($agent, $bot))) {
                return true;
            }
        }

        return false;
    }
}
