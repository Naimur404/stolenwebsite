@if (setting('payment_stripe_status') == 1)
    <li class="list-group-item">
        <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_stripe"
               value="stripe" @if ($selecting == STRIPE_PAYMENT_METHOD_NAME) checked @endif>
        <label for="payment_stripe" class="text-start">
            {{ setting('payment_stripe_name', trans('plugins/payment::payment.payment_via_card')) }}
        </label>
        <div class="payment_stripe_wrap payment_collapse_wrap collapse @if ($selecting == STRIPE_PAYMENT_METHOD_NAME) show @endif" style="padding: 15px 0;">
            <p>{!! BaseHelper::clean(get_payment_setting('description', STRIPE_PAYMENT_METHOD_NAME)) !!}</p>
            @if (get_payment_setting('payment_type', STRIPE_PAYMENT_METHOD_NAME, 'stripe_api_charge') == 'stripe_api_charge')
                <div class="card-checkout" style="max-width: 350px">
                    <div class="form-group mt-3 mb-3">
                        <div class="stripe-card-wrapper"></div>
                    </div>
                    <div class="form-group mb-3 @if ($errors->has('number') || $errors->has('expiry')) has-error @endif">
                        <div class="row">
                            <div class="col-sm-8">
                                <input placeholder="{{ trans('plugins/payment::payment.card_number') }}"
                                       class="form-control" type="text" id="stripe-number" data-stripe="number" autocomplete="off">
                            </div>
                            <div class="col-sm-4">
                                <input placeholder="{{ trans('plugins/payment::payment.mm_yy') }}" class="form-control"
                                       type="text" id="stripe-exp" data-stripe="exp" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3 @if ($errors->has('name') || $errors->has('cvc')) has-error @endif">
                        <div class="row">
                            <div class="col-sm-8">
                                <input placeholder="{{ trans('plugins/payment::payment.full_name') }}"
                                       class="form-control" id="stripe-name" type="text" data-stripe="name" autocomplete="off">
                            </div>
                            <div class="col-sm-4">
                                <input placeholder="{{ trans('plugins/payment::payment.cvc') }}" class="form-control"
                                       type="text" id="stripe-cvc" data-stripe="cvc" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="payment-stripe-key" data-value="{{ setting('payment_stripe_client_id') }}"></div>
            @endif

            @php $supportedCurrencies = (new \Botble\Stripe\Services\Gateways\StripePaymentService)->supportedCurrencyCodes(); @endphp
            @if (!in_array(get_application_currency()->title, $supportedCurrencies) && ! get_application_currency()->replicate()->newQuery()->where('title', 'USD')->exists())
                <div class="alert alert-warning" style="margin-top: 15px;">
                    {{ __(":name doesn't support :currency. List of currencies supported by :name: :currencies.", ['name' => 'Stripe', 'currency' => get_application_currency()->title, 'currencies' => implode(', ', $supportedCurrencies)]) }}
                    @php
                        $currencies = get_all_currencies();

                        $currencies = $currencies->filter(function ($item) use ($supportedCurrencies) { return in_array($item->title, $supportedCurrencies); });
                    @endphp
                    @if (count($currencies))
                        <div style="margin-top: 10px;">{{ __('Please switch currency to any supported currency') }}:&nbsp;&nbsp;
                            @foreach ($currencies as $currency)
                                <a href="{{ route('public.change-currency', $currency->title) }}" @if (get_application_currency_id() == $currency->id) class="active" @endif><span>{{ $currency->title }}</span></a>
                                @if (!$loop->last)
                                    &nbsp; | &nbsp;
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </li>
@endif
