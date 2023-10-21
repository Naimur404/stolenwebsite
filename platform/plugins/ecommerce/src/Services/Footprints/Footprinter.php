<?php

namespace Botble\Ecommerce\Services\Footprints;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class Footprinter implements FootprinterInterface
{
    protected ?Request $request = null;

    protected string $random;

    public function __construct()
    {
        $this->random = Str::random(20); // Will only be set once during requests since this class is a singleton
    }

    public function footprint(Request $request): string
    {
        $this->request = $request;

        $cookieName = 'botble_footprints_cookie';

        if ($request->hasCookie($cookieName)) {
            return $request->cookie($cookieName);
        }

        // This will add the cookie to the response
        Cookie::queue(
            $cookieName,
            $footprint = $this->fingerprint(),
            604800,
            null,
            config('session.domain')
        );

        return $footprint;
    }

    /**
     * This method will generate a fingerprint for the request based on the configuration.
     *
     * If relying on cookies then the logic of this function is not important, but if cookies are disabled this value
     * will be used to link previous requests with one another.
     */
    protected function fingerprint(): string
    {
        // This is highly inspired from the $request->fingerprint() method
        return sha1(implode('|', array_filter([
            $this->request->ip(),
            $this->request->header('User-Agent'),
            $this->random,
        ])));
    }

    public function getFootprints(): array
    {
        $data = Cookie::get('botble_footprints_cookie_data');

        if (! $data) {
            return [];
        }

        return json_decode($data, true);
    }
}
