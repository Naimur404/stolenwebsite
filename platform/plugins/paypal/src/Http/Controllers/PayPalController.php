<?php

namespace Botble\PayPal\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Supports\PaymentHelper;
use Botble\PayPal\Http\Requests\PayPalPaymentCallbackRequest;
use Botble\PayPal\Services\Gateways\PayPalPaymentService;
use Illuminate\Routing\Controller;

class PayPalController extends Controller
{
    public function getCallback(
        PayPalPaymentCallbackRequest $request,
        PayPalPaymentService $payPalPaymentService,
        BaseHttpResponse $response
    ) {
        $status = $payPalPaymentService->getPaymentStatus($request);

        if (! $status) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->withInput()
                ->setMessage(__('Payment failed!'));
        }

        $payPalPaymentService->afterMakePayment($request->input());

        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL())
            ->setMessage(__('Checkout successfully!'));
    }
}
