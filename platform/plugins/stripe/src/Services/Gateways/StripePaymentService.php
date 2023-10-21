<?php

namespace Botble\Stripe\Services\Gateways;

use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Stripe\Services\Abstracts\StripePaymentAbstract;
use Botble\Stripe\Supports\StripeHelper;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Checkout\Session as StripeCheckoutSession;

class StripePaymentService extends StripePaymentAbstract
{
    public function makePayment(array $data): string|null
    {
        $request = request();
        $this->amount = $data['amount'];
        $this->currency = strtoupper($data['currency']);

        $this->setClient();

        if ($this->isStripeApiCharge()) {
            if (! $this->token) {
                $this->setErrorMessage(trans('plugins/payment::payment.could_not_get_stripe_token'));

                Log::error(
                    trans('plugins/payment::payment.could_not_get_stripe_token'),
                    PaymentHelper::formatLog(
                        [
                            'error' => 'missing Stripe token',
                            'last_4_digits' => $request->input('last4Digits'),
                            'name' => $request->input('name'),
                            'client_IP' => $request->input('clientIP'),
                            'time_created' => $request->input('timeCreated'),
                            'live_mode' => $request->input('liveMode'),
                        ],
                        __LINE__,
                        __FUNCTION__,
                        __CLASS__
                    )
                );

                return null;
            }

            $charge = Charge::create([
                'amount' => $this->convertAmount($this->amount),
                'currency' => $this->currency,
                'source' => $this->token,
                'description' => trans('plugins/payment::payment.payment_description', [
                    'order_id' => implode(', #', $data['order_id']),
                    'site_url' => $request->getHost(),
                ]),
                'metadata' => ['order_id' => json_encode($data['order_id'])],
            ]);

            $this->chargeId = $charge['id'];

            if ($this->chargeId) {
                $this->afterMakePayment($this->chargeId, $data);
            }

            return $this->chargeId;
        }

        $lineItems = [];

        foreach ($data['products'] as $product) {
            $lineItems[] = [
                'price_data' => [
                    'product_data' => [
                        'name' => $product['name'],
                        'metadata' => [
                            'pro_id' => $product['id'],
                        ],
                        'description' => $product['name'],
                    ],
                    'unit_amount' => $this->convertAmount($product['price_per_order'] / $product['qty']),
                    'currency' => $this->currency,
                ],
                'quantity' => $product['qty'],
            ];
        }

        $requestData = [
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('payments.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payments.stripe.error'),
            'metadata' => [
                'order_id' => json_encode($data['order_id']),
                'amount' => $this->amount,
                'currency' => $this->currency,
                'customer_id' => Arr::get($data, 'customer_id'),
                'customer_type' => Arr::get($data, 'customer_type'),
                'return_url' => Arr::get($data, 'return_url'),
                'callback_url' => Arr::get($data, 'callback_url'),
            ],
        ];

        if (! empty($data['shipping_method'])) {
            $requestData['shipping_options'] = [
                [
                    'shipping_rate_data' => [
                        'type' => 'fixed_amount',
                        'fixed_amount' => [
                            'amount' => $this->convertAmount($data['shipping_amount']),
                            'currency' => $this->currency,
                        ],
                        'display_name' => $data['shipping_method'],
                    ],
                ],
            ];
        }

        $checkoutSession = StripeCheckoutSession::create($requestData);

        return $checkoutSession->url;
    }

    protected function convertAmount(float $amount): int
    {
        $multiplier = StripeHelper::getStripeCurrencyMultiplier($this->currency);

        if ($multiplier > 1) {
            $amount = round($amount, 2) * $multiplier;
        }

        return (int)$amount;
    }

    public function afterMakePayment(string $chargeId, array $data): string
    {
        try {
            $payment = $this->getPaymentDetails($chargeId);
            if ($payment && ($payment->paid || $payment->status == 'succeeded')) {
                $paymentStatus = PaymentStatusEnum::COMPLETED;
            } else {
                $paymentStatus = PaymentStatusEnum::FAILED;
            }
        } catch (Exception) {
            $paymentStatus = PaymentStatusEnum::FAILED;
        }

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'charge_id' => $chargeId,
            'order_id' => (array)$data['order_id'],
            'customer_id' => Arr::get($data, 'customer_id'),
            'customer_type' => Arr::get($data, 'customer_type'),
            'payment_channel' => STRIPE_PAYMENT_METHOD_NAME,
            'status' => $paymentStatus,
        ]);

        return $chargeId;
    }

    public function isStripeApiCharge(): bool
    {
        $key = 'stripe_api_charge';

        return get_payment_setting('payment_type', STRIPE_PAYMENT_METHOD_NAME, $key) == $key;
    }

    public function supportedCurrencyCodes(): array
    {
        return [
            'USD',
            'AED',
            'AFN',
            'ALL',
            'AMD',
            'ANG',
            'AOA',
            'ARS',
            'AUD',
            'AWG',
            'AZN',
            'BAM',
            'BBD',
            'BDT',
            'BGN',
            'BHD',
            'BIF',
            'BMD',
            'BND',
            'BOB',
            'BRL',
            'BSD',
            'BWP',
            'BYN',
            'BZD',
            'CAD',
            'CDF',
            'CHF',
            'CLP',
            'CNY',
            'COP',
            'CRC',
            'CVE',
            'CZK',
            'DJF',
            'DKK',
            'DOP',
            'DZD',
            'EGP',
            'ETB',
            'EUR',
            'FJD',
            'FKP',
            'GBP',
            'GEL',
            'GIP',
            'GMD',
            'GNF',
            'GTQ',
            'GYD',
            'HKD',
            'HNL',
            'HRK',
            'HTG',
            'HUF',
            'IDR',
            'ILS',
            'INR',
            'ISK',
            'JMD',
            'JOD',
            'JPY',
            'KES',
            'KGS',
            'KHR',
            'KMF',
            'KRW',
            'KWD',
            'KYD',
            'KZT',
            'LAK',
            'LBP',
            'LKR',
            'LRD',
            'LSL',
            'MAD',
            'MDL',
            'MGA',
            'MKD',
            'MMK',
            'MNT',
            'MOP',
            'MRO',
            'MUR',
            'MVR',
            'MWK',
            'MXN',
            'MYR',
            'MZN',
            'NAD',
            'NGN',
            'NIO',
            'NOK',
            'NPR',
            'NZD',
            'OMR',
            'PAB',
            'PEN',
            'PGK',
            'PHP',
            'PKR',
            'PLN',
            'PYG',
            'QAR',
            'RON',
            'RSD',
            'RUB',
            'RWF',
            'SAR',
            'SBD',
            'SCR',
            'SEK',
            'SGD',
            'SHP',
            'SLE',
            'SOS',
            'SRD',
            'STD',
            'SZL',
            'THB',
            'TJS',
            'TND',
            'TOP',
            'TRY',
            'TTD',
            'TWD',
            'TZS',
            'UAH',
            'UGX',
            'UYU',
            'UZS',
            'VND',
            'VUV',
            'WST',
            'XAF',
            'XCD',
            'XOF',
            'XPF',
            'YER',
            'ZAR',
            'ZMW',
            'USDC',
            'BTN',
            'GHS',
            'EEK',
            'LVL',
            'SVC',
            'VEF',
            'LTL',
            'SLL',
        ];
    }
}
