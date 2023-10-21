<?php

namespace Botble\PayPal\Providers;

use Botble\Base\Facades\Html;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\PayPal\Services\Gateways\PayPalPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerPayPalMethod'], 2, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithPayPal'], 2, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['PAYPAL'] = PAYPAL_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == PAYPAL_PAYMENT_METHOD_NAME) {
                $value = 'PayPal';
            }

            return $value;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == PAYPAL_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 2, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == PAYPAL_PAYMENT_METHOD_NAME) {
                $data = PayPalPaymentService::class;
            }

            return $data;
        }, 2, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == PAYPAL_PAYMENT_METHOD_NAME) {
                $paymentDetail = (new PayPalPaymentService())->getPaymentDetails($payment->charge_id);
                $data = view('plugins/paypal::detail', ['payment' => $paymentDetail])->render();
            }

            return $data;
        }, 2, 2);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . view('plugins/paypal::settings')->render();
    }

    public function registerPayPalMethod(?string $html, array $data): string
    {
        PaymentMethods::method(PAYPAL_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/paypal::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithPayPal(array $data, Request $request): array
    {
        if ($data['type'] !== PAYPAL_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $currentCurrency = get_application_currency();

        $currencyModel = $currentCurrency->replicate();

        $payPalService = $this->app->make(PayPalPaymentService::class);

        $supportedCurrencies = $payPalService->supportedCurrencyCodes();

        $currency = strtoupper($currentCurrency->title);

        $notSupportCurrency = false;

        if (! in_array($currency, $supportedCurrencies)) {
            $notSupportCurrency = true;

            if (! $currencyModel->where('title', 'USD')->exists()) {
                $data['error'] = true;
                $data['message'] = __(
                    ":name doesn't support :currency. List of currencies supported by :name: :currencies.",
                    [
                        'name' => 'PayPal',
                        'currency' => $currency,
                        'currencies' => implode(', ', $supportedCurrencies),
                    ]
                );

                return $data;
            }
        }

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        if ($notSupportCurrency) {
            $usdCurrency = $currencyModel->where('title', 'USD')->first();

            $paymentData['currency'] = 'USD';
            if ($currentCurrency->is_default) {
                $paymentData['amount'] = $paymentData['amount'] * $usdCurrency->exchange_rate;
            } else {
                $paymentData['amount'] = format_price(
                    $paymentData['amount'] / $currentCurrency->exchange_rate,
                    $currentCurrency,
                    true
                );
            }
        }

        if (! $request->input('callback_url')) {
            $paymentData['callback_url'] = route('payments.paypal.status');
        }

        $checkoutUrl = $payPalService->execute($paymentData);

        if ($checkoutUrl) {
            $data['checkoutUrl'] = $checkoutUrl;
        } else {
            $data['error'] = true;
            $data['message'] = $payPalService->getErrorMessage();
        }

        return $data;
    }
}
