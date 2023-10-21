<?php

namespace Botble\Newsletter\Drivers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AbstractProvider
{
    protected string $apiUrl;

    public function __construct(protected string|null $apiKey, protected string|null $listId)
    {
    }

    protected function request(string $method, string $uri, array $data = []): Response
    {
        $request = Http::withoutVerifying()->withToken($this->apiKey);

        $uri = $this->apiUrl . $uri;

        return match ($method) {
            'POST' => $request->post($uri, $data),
            'PATCH' => $request->patch($uri, $data),
            'PUT' => $request->put($uri, $data),
            default => $request->get($uri, $data),
        };
    }
}
