<?php

namespace Botble\Paystack\Services;

use Exception;
use Unicodeveloper\Paystack\Paystack as BasePaystack;

class Paystack extends BasePaystack
{
    public function refundOrder($paymentId, $amount)
    {
        $relativeUrl = '/refund';

        $data = [
            'body' => json_encode([
                'transaction' => $paymentId,
                'amount' => $amount * 100,
            ]),
        ];

        $this->response = $this->client->post($this->baseUrl . $relativeUrl, $data);

        if ($this->isValid()) {
            return $this->getResponse();
        }

        throw new Exception('Invalid Refund Order Paystack');
    }

    protected function getResponse(): array
    {
        return json_decode($this->response->getBody(), true);
    }

    public function isValid(): bool
    {
        return $this->getResponse()['status'];
    }

    public function getPaymentDetails($transactionId)
    {
        $relativeUrl = '/transaction/' . $transactionId;

        $this->response = $this->client->get($this->baseUrl . $relativeUrl);

        if ($this->isValid()) {
            return $this->getResponse();
        }

        throw new Exception('Invalid Get Payment Details Paystack');
    }

    public function getListTransactions(array $params = [])
    {
        $relativeUrl = '/transaction' . ($params ? ('?' . http_build_query($params)) : '');

        $this->response = $this->client->get($this->baseUrl . $relativeUrl);

        if ($this->isValid()) {
            return $this->getResponse();
        }

        throw new Exception('Invalid Get List Transactions Paystack');
    }

    public function getRefundDetails($refundId)
    {
        $relativeUrl = '/refund/' . $refundId;

        $this->response = $this->client->get($this->baseUrl . $relativeUrl);

        if ($this->isValid()) {
            return $this->getResponse();
        }

        throw new Exception('Invalid Refund Order Paystack');
    }
}
