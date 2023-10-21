<?php

namespace Botble\PayPalPayout\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Enums\PayoutPaymentMethodsEnum;
use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Botble\Marketplace\Repositories\Interfaces\WithdrawalInterface;
use Botble\PayPal\Services\Gateways\PayPalPaymentService;
use Botble\PayPalPayout\PayPalPayoutsSDK\Payouts\PayoutsGetRequest;
use Botble\PayPalPayout\PayPalPayoutsSDK\Payouts\PayoutsPostRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Throwable;

class PayPalPayoutController extends Controller
{
    public function make(
        string $withdrawalId,
        PayPalPaymentService $payPalPaymentService,
        WithdrawalInterface $withdrawalRepository,
        BaseHttpResponse $response
    ) {
        $withdrawal = $withdrawalRepository->findOrFail($withdrawalId);

        if ($withdrawal->payment_channel != PayoutPaymentMethodsEnum::PAYPAL) {
            return $response
                ->setError()
                ->setMessage(__('Payout method is not accepted!'));
        }

        $totalAmount = round((float)$withdrawal->amount, 2);

        $payPalId = Arr::get($withdrawal->bank_info, 'paypal_id');

        if (! $payPalId) {
            return $response
                ->setError()
                ->setMessage(__('PayPal ID is not set!'));
        }

        try {
            $client = $payPalPaymentService->getClient();

            $request = new PayoutsPostRequest();
            $request->body = json_decode(
                '{
                "sender_batch_header":
                {
                  "email_subject": "' . __('You have money!') . '",
                  "email_message": "' . __('You received a payment. Thanks for selling on our site!') . '"
                },
                "items": [
                {
                      "recipient_type": "EMAIL",
                      "amount": {
                        "value": "' . ((string)$totalAmount) . '",
                        "currency": "' . $withdrawal->currency . '"
                      },
                      "note": "Thanks for selling on our site!",
                      "sender_item_id": "' . $withdrawal->id . '",
                      "receiver": "' . $payPalId . '"
                  }
                ]
              }',
                true
            );
            $result = $client->execute($request);

            $withdrawal->status = WithdrawalStatusEnum::COMPLETED;
            $withdrawal->transaction_id = $result->result->batch_header->payout_batch_id;
            $withdrawal->save();

            return $response->setMessage(__('Processed PayPal payout successfully!'));
        } catch (Throwable $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function retrieve(string $batchId, PayPalPaymentService $payPalPaymentService, BaseHttpResponse $response)
    {
        try {
            $client = $payPalPaymentService->getClient();

            $request = new PayoutsGetRequest($batchId);
            $result = $client->execute($request);

            $batchHeader = $result->result->batch_header;

            $data = [
                'transactionId' => $batchHeader->payout_batch_id,
                'status' => $batchHeader->batch_status,
                'amount' => $batchHeader->amount->value . $batchHeader->amount->currency,
                'fee' => $batchHeader->fees->value . $batchHeader->fees->currency,
                'createdAt' => $batchHeader->time_created,
                'completedAt' => $batchHeader->time_completed,
                'fundingSource' => $batchHeader->funding_source,
            ];

            return $response
                ->setData([
                    'html' => view('plugins/paypal-payout::payout-transaction-detail', $data)->render(),
                    'meta' => $result->result,
                ]);
        } catch (Throwable $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
