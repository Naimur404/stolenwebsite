<?php

namespace Botble\Newsletter\Drivers;

use Botble\Newsletter\Contracts\Provider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MailChimp extends AbstractProvider implements Provider
{
    protected string $apiUrl = 'https://{region}.api.mailchimp.com/3.0';

    public function contacts(): array
    {
        $response = $this->request('GET', '/lists');

        return Arr::get($response->json(), 'lists') ?: [];
    }

    public function subscribe(string $email, array $mergeFields = []): array
    {
        $params = [
            'email_address' => $email,
            'status' => 'subscribed',
            'email_type' => 'html',
        ];

        if (count($mergeFields)) {
            $params['merge_fields'] = $mergeFields;
        }

        $response = $this->request('PUT', "/lists/$this->listId/members/" . $this->subscriberHash($email), $params);

        return $response->json();
    }

    public function unsubscribe(string $email): array
    {
        $response = $this->request('PATCH', "/lists/$this->listId/members/" . $this->subscriberHash($email), [
            'status' => 'unsubscribed',
        ]);

        return $response->json();
    }

    protected function request(string $method, string $uri, array $data = []): Response
    {
        $this->apiUrl = str_replace('{region}', Str::of($this->apiKey)->after('-'), $this->apiUrl);

        return parent::request($method, $uri, $data);
    }

    protected function subscriberHash(string $email): string
    {
        return md5(strtolower($email));
    }
}
