<?php

namespace Botble\Stripe\Providers;

use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Currency;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Stripe\Services\Gateways\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerStripeMethod'], 1, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithStripe'], 1, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 1);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['STRIPE'] = STRIPE_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 1, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == STRIPE_PAYMENT_METHOD_NAME) {
                $value = 'Stripe';
            }

            return $value;
        }, 1, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == STRIPE_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 1, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == STRIPE_PAYMENT_METHOD_NAME) {
                $data = StripePaymentService::class;
            }

            return $data;
        }, 1, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == STRIPE_PAYMENT_METHOD_NAME) {
                $paymentDetail = (new StripePaymentService())->getPaymentDetails($payment->charge_id);

                $data = view('plugins/stripe::detail', ['payment' => $paymentDetail])->render();
            }

            return $data;
        }, 1, 2);

        if (defined('PAYMENT_FILTER_FOOTER_ASSETS')) {
            add_filter(PAYMENT_FILTER_FOOTER_ASSETS, function ($data) {
                if ($this->app->make(StripePaymentService::class)->isStripeApiCharge()) {
                    return $data . view('plugins/stripe::assets')->render();
                }

                return $data;
            }, 1);
        }
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . view('plugins/stripe::settings')->render();
    }

    public function registerStripeMethod(?string $html, array $data): string
    {
        PaymentMethods::method(STRIPE_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/stripe::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithStripe(array $data, Request $request): array
    {
        if ($data['type'] !== STRIPE_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $stripePaymentService = $this->app->make(StripePaymentService::class);

        $currentCurrency = get_application_currency();

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        if (strtoupper($currentCurrency->title) !== 'USD') {
            $supportedCurrency = Currency::query()->where('title', 'USD')->first();

            $paymentData['currency'] = 'USD';
            if ($currentCurrency->is_default) {
                $paymentData['amount'] = $paymentData['amount'] * $supportedCurrency->exchange_rate;
            } else {
                $paymentData['amount'] = format_price(
                    $paymentData['amount'] / $currentCurrency->exchange_rate,
                    $currentCurrency,
                    true
                );
            }
        }

        $supportedCurrencies = $stripePaymentService->supportedCurrencyCodes();

        if (! in_array($paymentData['currency'], $supportedCurrencies)) {
            $data['error'] = true;
            $data['message'] = __(
                ":name doesn't support :currency. List of currencies supported by :name: :currencies.",
                [
                    'name' => 'Stripe',
                    'currency' => $paymentData['currency'],
                    'currencies' => implode(', ', $supportedCurrencies),
                ]
            );

            return $data;
        }

        $result = $stripePaymentService->execute($paymentData);

        if ($stripePaymentService->getErrorMessage()) {
            $data['error'] = true;
            $data['message'] = $stripePaymentService->getErrorMessage();
        } elseif ($result) {
            if ($stripePaymentService->isStripeApiCharge()) {
                $data['charge_id'] = $result;
            } else {
                $data['checkoutUrl'] = $result;
            }
        }

        return $data;
    }
}
