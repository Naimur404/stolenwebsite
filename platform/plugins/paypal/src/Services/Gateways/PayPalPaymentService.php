<?php

namespace Botble\PayPal\Services\Gateways;

use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Botble\PayPal\Services\Abstracts\PayPalPaymentAbstract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PayPalPaymentService extends PayPalPaymentAbstract
{
    public function makePayment(array $data)
    {
        $amount = round((float)$data['amount'], $this->isSupportedDecimals() ? 2 : 0);

        $currency = $data['currency'];
        $currency = strtoupper($currency);

        $queryParams = [
            'type' => PAYPAL_PAYMENT_METHOD_NAME,
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $data['order_id'],
            'customer_id' => Arr::get($data, 'customer_id'),
            'customer_type' => Arr::get($data, 'customer_type'),
        ];

        if ($cancelUrl = $data['return_url'] ?: PaymentHelper::getCancelURL()) {
            $this->setCancelUrl($cancelUrl);
        }

        $description = Str::limit($data['description'], 50);

        return $this
            ->setReturnUrl($data['callback_url'] . '?' . http_build_query($queryParams))
            ->setCurrency($currency)
            ->setCustomer(Arr::get($data, 'address.email'))
            ->setItem([
                'name' => $description,
                'quantity' => 1,
                'price' => $amount,
                'sku' => null,
                'type' => PAYPAL_PAYMENT_METHOD_NAME,
            ])
            ->createPayment($description);
    }

    public function afterMakePayment(array $data): string|null
    {
        $status = PaymentStatusEnum::COMPLETED;

        $chargeId = session('paypal_payment_id');

        $orderIds = (array)Arr::get($data, 'order_id', []);

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'charge_id' => $chargeId,
            'order_id' => $orderIds,
            'customer_id' => Arr::get($data, 'customer_id'),
            'customer_type' => Arr::get($data, 'customer_type'),
            'payment_channel' => PAYPAL_PAYMENT_METHOD_NAME,
            'status' => $status,
        ]);

        session()->forget('paypal_payment_id');

        return $chargeId;
    }
}
