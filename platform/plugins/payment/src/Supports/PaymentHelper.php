<?php

namespace Botble\Payment\Supports;

use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class PaymentHelper
{
    public static function getRedirectURL(?string $checkoutToken = null): string
    {
        return apply_filters(PAYMENT_FILTER_REDIRECT_URL, $checkoutToken, route('public.index'));
    }

    public static function getCancelURL(?string $checkoutToken = null): string
    {
        return apply_filters(PAYMENT_FILTER_CANCEL_URL, $checkoutToken, route('public.index'));
    }

    public static function storeLocalPayment(array $args = [])
    {
        $data = array_merge([
            'user_id' => Auth::check() ? Auth::id() : 0,
        ], $args);

        $orderIds = (array)$data['order_id'];

        $payment = app(PaymentInterface::class)->getFirstBy([
            'charge_id' => $data['charge_id'],
            ['order_id', 'IN', $orderIds],
        ]);

        if ($payment) {
            return false;
        }

        $paymentChannel = Arr::get($data, 'payment_channel', PaymentMethodEnum::COD);

        return app(PaymentInterface::class)->create([
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'charge_id' => $data['charge_id'],
            'order_id' => Arr::first($orderIds),
            'customer_id' => Arr::get($data, 'customer_id'),
            'customer_type' => Arr::get($data, 'customer_type'),
            'payment_channel' => $paymentChannel,
            'status' => Arr::get($data, 'status', PaymentStatusEnum::PENDING),
        ]);
    }

    public static function formatLog(array $input, string|int $line = '', string $function = '', string $class = ''): array
    {
        return array_merge($input, [
            'user_id' => Auth::check() ? Auth::id() : 0,
            'ip' => Request::ip(),
            'line' => $line,
            'function' => $function,
            'class' => $class,
            'userAgent' => Request::header('User-Agent'),
        ]);
    }

    public static function defaultPaymentMethod(): string
    {
        return setting('default_payment_method', PaymentMethodEnum::COD);
    }
}
