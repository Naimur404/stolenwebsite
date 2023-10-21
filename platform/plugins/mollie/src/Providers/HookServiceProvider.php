<?php

namespace Botble\Mollie\Providers;

use Botble\Base\Facades\Html;
use Botble\Mollie\Services\Gateways\MolliePaymentService;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Payment\Supports\PaymentHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Mollie\Laravel\Facades\Mollie;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerMollieMethod'], 17, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithMollie'], 17, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 99);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['MOLLIE'] = MOLLIE_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 23, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MOLLIE_PAYMENT_METHOD_NAME) {
                $value = 'Mollie';
            }

            return $value;
        }, 23, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MOLLIE_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 23, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == MOLLIE_PAYMENT_METHOD_NAME) {
                $data = MolliePaymentService::class;
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == MOLLIE_PAYMENT_METHOD_NAME) {
                try {
                    $paymentService = (new MolliePaymentService());
                    $paymentDetail = $paymentService->getPaymentDetails($payment->charge_id);
                    if ($paymentDetail) {
                        $data = view('plugins/mollie::detail', ['payment' => $paymentDetail])->render();
                    }
                } catch (Exception) {
                    return $data;
                }
            }

            return $data;
        }, 20, 2);
    }

    public function addPaymentSettings(string|null $settings): string
    {
        return $settings . view('plugins/mollie::settings')->render();
    }

    public function registerMollieMethod(string|null $html, array $data): string|null
    {
        PaymentMethods::method(MOLLIE_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/mollie::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithMollie(array $data, Request $request)
    {
        if ($data['type'] !== MOLLIE_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        $orderIds = $paymentData['order_id'];

        $orderCodes = collect($orderIds)->map(function ($item) {
            return get_order_code($item);
        });

        try {
            $api = Mollie::api();

            $response = $api->payments()->create([
                'amount' => [
                    'currency' => $paymentData['currency'],
                    'value' => number_format((float)$paymentData['amount'], 2, '.', ''),
                ],
                'description' => 'Order(s) ' . $orderCodes->implode(', '),
                'redirectUrl' => PaymentHelper::getRedirectURL(),
                'webhookUrl' => route('mollie.payment.callback'),
                'metadata' => [
                    'order_id' => $orderIds,
                    'customer_id' => $paymentData['customer_id'],
                    'customer_type' => $paymentData['customer_type'],
                ],
            ]);

            header('Location: ' . $response->getCheckoutUrl());
            exit;
        } catch (Exception $exception) {
            $data['error'] = true;
            $data['message'] = $exception->getMessage();
        }

        return $data;
    }
}
