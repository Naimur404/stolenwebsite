<?php

namespace Botble\SslCommerz\Library\SslCommerz;

interface SslCommerzInterface
{
    public function makePayment(array $requestData);

    public function orderValidate(array|null $postData, string $transactionId, float $amount, string $currency = 'BDT');

    public function setParams(array $data);

    public function setRequiredInfo(array $data);

    public function setCustomerInfo(array $data);

    public function setShipmentInfo(array $data);

    public function setProductInfo(array $data);

    public function setAdditionalInfo(array $data);

    public function callToApi(array $data, array $header = [], bool $setLocalhost = false): bool|string;
}
