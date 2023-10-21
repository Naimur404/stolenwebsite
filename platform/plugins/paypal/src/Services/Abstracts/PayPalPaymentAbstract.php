<?php

namespace Botble\PayPal\Services\Abstracts;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use PayPalHttp\HttpResponse;

abstract class PayPalPaymentAbstract
{
    use PaymentErrorTrait;

    protected array $itemList;

    protected string $paymentCurrency;

    protected float $totalAmount;

    protected string $returnUrl;

    protected string $cancelUrl;

    protected PayPalHttpClient $client;

    protected string $transactionDescription;

    protected string $customer;

    protected bool $supportRefundOnline;

    public function __construct()
    {
        $this->paymentCurrency = config('plugins.payment.payment.currency');

        $this->totalAmount = 0;

        $this->setClient();

        $this->supportRefundOnline = true;
    }

    public function getSupportRefundOnline(): bool
    {
        return $this->supportRefundOnline;
    }

    /**
     * Returns PayPal HTTP client instance with environment which has access
     * credentials context. This can be used invoke PayPal API's provided the
     * credentials have the access to do so.
     */
    public function setClient(): self
    {
        $this->client = new PayPalHttpClient($this->environment());

        return $this;
    }

    public function getClient(): PayPalHttpClient
    {
        return $this->client;
    }

    /**
     * Setting up and Returns PayPal SDK environment with PayPal Access credentials.
     * For demo purpose, we are using SandboxEnvironment. In production this will be
     * ProductionEnvironment.
     */
    public function environment(): SandboxEnvironment|ProductionEnvironment
    {
        $clientId = setting('payment_paypal_client_id', '<<PAYPAL-CLIENT-ID>>');
        $clientSecret = setting('payment_paypal_client_secret', '<<PAYPAL-CLIENT-SECRET>>');
        $payPalMode = setting('payment_paypal_mode');

        if ($payPalMode) {
            return new ProductionEnvironment($clientId, $clientSecret);
        }

        return new SandboxEnvironment($clientId, $clientSecret);
    }

    public function setCurrency(string $currency): self
    {
        $this->paymentCurrency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->paymentCurrency;
    }

    public function getCustomer(): string
    {
        return $this->customer;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function setItem(array $itemData): self
    {
        if (count($itemData) === count($itemData, COUNT_RECURSIVE)) {
            $itemData = [$itemData];
        }

        foreach ($itemData as $data) {
            $amount = $data['price'] * $data['quantity'];

            $item = [
                'name' => $data['name'],
                'sku' => $data['sku'],
                'unit_amount' => [
                    'currency_code' => $this->paymentCurrency,
                    'value' => $amount,
                ],
                'quantity' => $data['quantity'],
            ];

            if ($description = Arr::get($data, 'description')) {
                $item['description'] = $description;
            }

            if ($tax = Arr::get($data, 'tax')) {
                $item['tax'] = [
                    'currency_code' => $this->paymentCurrency,
                    'value' => $tax,
                ];
            }

            if ($category = Arr::get($data, 'category')) {
                $item['category'] = $category;
            }

            $this->itemList[] = $item;
            $this->totalAmount += $amount;
        }

        // issue https://developer.paypal.com/docs/api/orders/v2/#error-DECIMAL_PRECISION
        $this->totalAmount = round((float)$this->totalAmount, $this->isSupportedDecimals() ? 2 : 0);

        return $this;
    }

    public function setReturnUrl(string $url): self
    {
        $this->returnUrl = $url;

        return $this;
    }

    public function setCancelUrl(string $url): self
    {
        $this->cancelUrl = $url;

        return $this;
    }

    /**
     * Setting up the JSON request body for creating the Order. The Intent in the
     * request body should be set as "CAPTURE" for capture intent flow.
     */
    protected function buildRequestBody(): array
    {
        return [
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => $this->returnUrl,
                'cancel_url' => $this->cancelUrl ?: $this->returnUrl,
                'brand_name' => theme_option('site_title'),
            ],
            'purchase_units' => [
                0 => [
                    'description' => $this->transactionDescription,
                    'custom_id' => $this->customer,
                    'amount' => [
                        'currency_code' => $this->paymentCurrency,
                        'value' => (string)$this->totalAmount,
                    ],
                ],
            ],
        ];
    }

    public function createPayment(string $transactionDescription): string|null|bool
    {
        $this->transactionDescription = $transactionDescription;

        $orderRequest = new OrdersCreateRequest();
        $orderRequest->prefer('return=representation');
        $orderRequest->body = $this->buildRequestBody();
        $checkoutUrl = '';
        $paymentId = null;

        try {
            // Call API with your client and get a response for your call
            $response = $this->client->execute($orderRequest);
            if ($response && $response->statusCode == 201) {
                // @phpstan-ignore-next-line
                $paymentId = $response->result->id;

                // @phpstan-ignore-next-line
                foreach ($response->result->links as $link) {
                    if ($link->rel == 'approve') {
                        $checkoutUrl = $link->href;
                    }
                }
            }
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }

        if ($checkoutUrl && $paymentId) {
            session(['paypal_payment_id' => $paymentId]);

            return $checkoutUrl;
        }

        session()->forget('paypal_payment_id');

        return null;
    }

    public function getPaymentStatus(Request $request)
    {
        if (empty($request->input('PayerID')) || empty($request->input('token'))) {
            return false;
        }

        $paymentId = session('paypal_payment_id');

        try {
            $orderRequest = new OrdersCaptureRequest($paymentId);
            $orderRequest->prefer('return=representation');

            $response = $this->client->execute($orderRequest);

            // @phpstan-ignore-next-line
            if ($response && $response->statusCode == 201 && $response->result->status == 'COMPLETED') {
                // @phpstan-ignore-next-line
                return $response->result->status;
            }
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);
        }

        return false;
    }

    public function getPaymentDetails(string $paymentId): bool|HttpResponse
    {
        try {
            $response = $this->client->execute(new OrdersGetRequest($paymentId));
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }

        return $response;
    }

    /**
     * Function to create a refund capture request. Payload can be updated to issue partial refund.
     */
    public function buildRefundRequestBody(float|int|string $totalAmount): array
    {
        $totalAmount = round((float) $totalAmount, 2);

        return [
            'amount' => [
                'value' => (string) $totalAmount,
                'currency_code' => $this->paymentCurrency,
            ],
        ];
    }

    /**
     * This function can be used to preform refund on the capture.
     */
    public function refundOrder(string $paymentId, float|int|string $totalAmount): array
    {
        try {
            $detail = $this->getPaymentDetails($paymentId);
            $captureId = null;
            if ($detail) {
                // @phpstan-ignore-next-line
                $purchase = Arr::get($detail->result->purchase_units, 0);
                $capture = Arr::get($purchase->payments->captures, 0);
                $captureId = $capture->id;
            }
            if ($captureId) {
                $refundRequest = new CapturesRefundRequest($captureId);
                $refundRequest->body = $this->buildRefundRequestBody($totalAmount);
                $refundRequest->prefer('return=representation');
                $response = $this->client->execute($refundRequest);

                // @phpstan-ignore-next-line
                if ($response && $response->statusCode == 201 && $response->result->status == 'COMPLETED') {
                    return [
                        'error' => false, // @phpstan-ignore-next-line
                        'status' => $response->result->status,
                        'data' => (array) $response->result,
                    ];
                }

                return [
                    'error' => true,
                    'status' => $response->statusCode,
                    'message' => trans('plugins/payment::payment.status_is_not_completed'),
                ];
            }

            return [
                'error' => true,
                'message' => trans('plugins/payment::payment.cannot_found_capture_id'),
            ];
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function execute(array $data)
    {
        try {
            return $this->makePayment($data);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }
    }

    public function isSupportedDecimals(): bool
    {
        return ! in_array($this->getCurrency(), [
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF',
        ]);
    }

    /**
     * List currencies supported https://developer.paypal.com/docs/api/reference/currency-codes/
     */
    public function supportedCurrencyCodes(): array
    {
        return [
            'AUD',
            'BRL',
            'CAD',
            'CNY',
            'CZK',
            'DKK',
            'EUR',
            'HKD',
            'HUF',
            'ILS',
            'JPY',
            'MYR',
            'MXN',
            'TWD',
            'NZD',
            'NOK',
            'PHP',
            'PLN',
            'GBP',
            'RUB',
            'SGD',
            'SEK',
            'CHF',
            'THB',
            'USD',
        ];
    }

    abstract public function makePayment(array $data);

    abstract public function afterMakePayment(array $data);
}
