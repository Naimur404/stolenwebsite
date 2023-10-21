<?php

namespace Botble\SslCommerz\Services\Abstracts;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\SslCommerz\Services\SslCommerz;
use Botble\Support\Services\ProduceServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

abstract class SslCommerzPaymentAbstract implements ProduceServiceInterface
{
    use PaymentErrorTrait;

    protected string $paymentCurrency;

    protected SslCommerz $client;

    protected bool $supportRefundOnline = true;

    protected int $totalAmount = 0;

    public function __construct()
    {
        $this->paymentCurrency = config('plugins.payment.payment.currency');

        $this->setClient();
    }

    public function getSupportRefundOnline(): bool
    {
        return $this->supportRefundOnline;
    }

    public function setClient(): static
    {
        $this->client = new SslCommerz();

        return $this;
    }

    public function getClient(): SslCommerz
    {
        return $this->client;
    }

    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $payment = $this->client->getPaymentDetails($paymentId);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return ['status' => 'error'];
        }

        return $payment;
    }

    public function refundOrder(string $paymentId, float $amount, array $options = []): array
    {
        try {
            $detail = $this->client->getPaymentDetails($paymentId);
            $bankTranId = Arr::get($detail, 'element.0.bank_tran_id');
            if ($bankTranId) {
                $response = $this->client->refundOrder($bankTranId, $amount, $options);
                $status = Arr::get($response, 'status');
                if ($status == 'success') {
                    $response = array_merge($response, ['_refund_id' => Arr::get($response, 'refund_ref_id')]);

                    return [
                        'error' => false,
                        'message' => $status,
                        'data' => $response,
                    ];
                }

                return [
                    'error' => true,
                    'message' => trans('plugins/payment::payment.status_is_not_completed'),
                ];
            }

            return [
                'error' => true,
                'message' => sprintf('Payment %s can not found bank_tran_id', $paymentId),
            ];
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function refundDetail(string $refundRefId): array
    {
        try {
            $response = $this->client->refundDetail($refundRefId);
            $status = Arr::get($response, 'status');

            return [
                'error' => false,
                'message' => $status,
                'data' => $response,
                'status' => $status,
            ];
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function execute(Request $request)
    {
        try {
            return $this->makePayment($request);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }
    }

    abstract public function makePayment(Request $request);

    abstract public function afterMakePayment(Request $request);
}
