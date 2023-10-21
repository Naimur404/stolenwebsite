<?php

namespace Botble\Newsletter\Drivers;

use Botble\Newsletter\Contracts\Provider;
use Illuminate\Support\Arr;

class SendGrid extends AbstractProvider implements Provider
{
    protected string $apiUrl = 'https://api.sendgrid.com/v3';

    public function contacts(): array
    {
        $response = $this->request('GET', '/marketing/lists');

        return Arr::get($response->json(), 'result') ?: [];
    }

    public function subscribe(string $email, array $mergeFields = []): array
    {
        $response = $this->request('PUT', '/marketing/contacts', [
            'list_ids' => [$this->listId],
            'contacts' => [
                array_merge($mergeFields, [
                    'email' => $email,
                ]),
            ],
        ]);

        return $response->json();
    }

    public function unsubscribe(string $email): array
    {
        return [];
    }
}
