@extends('plugins/ecommerce::orders.master')
@section('title')
    {{ __('Checkout') }}
@stop
@section('content')
    @if (Cart::instance('cart')->count() > 0)
        @php
            $rawTotal = Cart::instance('cart')->rawTotal();
            $orderAmount = max($rawTotal - $promotionDiscountAmount - $couponDiscountAmount, 0);
            $orderAmount += (float)$shippingAmount;
        @endphp

        @if (is_plugin_active('payment'))
            @include('plugins/payment::partials.header')
        @endif

        {!! Form::open(['route' => ['public.checkout.process', $token], 'class' => 'checkout-form payment-checkout-form', 'id' => 'checkout-form']) !!}
            <input type="hidden" name="checkout-token" id="checkout-token" value="{{ $token }}">

            <div class="container" id="main-checkout-product-info">
                <div class="row">
                    <div class="order-1 order-md-2 col-lg-5 col-md-6 right">
                        <div class="d-block d-sm-none">
                            @include('plugins/ecommerce::orders.partials.logo')
                        </div>
                        <div id="cart-item" class="position-relative">

                            <div class="payment-info-loading" style="display: none;">
                                <div class="payment-info-loading-content">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>

                            {!! apply_filters(RENDER_PRODUCTS_IN_CHECKOUT_PAGE, $products) !!}

                            <div class="mt-2 p-2">
                                <div class="row">
                                    <div class="col-6">
                                        <p>{{ __('Subtotal') }}:</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="price-text sub-total-text text-end"> {{ format_price(Cart::instance('cart')->rawSubTotal()) }} </p>
                                    </div>
                                </div>
                                @if (EcommerceHelper::isTaxEnabled())
                                    <div class="row">
                                        <div class="col-6">
                                            <p>{{ __('Tax') }}:</p>
                                        </div>
                                        <div class="col-6 float-end">
                                            <p class="price-text tax-price-text">{{ format_price(Cart::instance('cart')->rawTax()) }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if (session('applied_coupon_code'))
                                    <div class="row coupon-information">
                                        <div class="col-6">
                                            <p>{{ __('Coupon code') }}:</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="price-text coupon-code-text"> {{ session('applied_coupon_code') }} </p>
                                        </div>
                                    </div>
                                @endif
                                @if ($couponDiscountAmount > 0)
                                    <div class="row price discount-amount">
                                        <div class="col-6">
                                            <p>{{ __('Coupon code discount amount') }}:</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="price-text total-discount-amount-text"> {{ format_price($couponDiscountAmount) }} </p>
                                        </div>
                                    </div>
                                @endif
                                @if ($promotionDiscountAmount > 0)
                                    <div class="row">
                                        <div class="col-6">
                                            <p>{{ __('Promotion discount amount') }}:</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="price-text"> {{ format_price($promotionDiscountAmount) }} </p>
                                        </div>
                                    </div>
                                @endif
                                @if (!empty($shipping) && Arr::get($sessionCheckoutData, 'is_available_shipping', true))
                                    <div class="row">
                                        <div class="col-6">
                                            <p>{{ __('Shipping fee') }}:</p>
                                        </div>
                                        <div class="col-6 float-end">
                                            <p class="price-text shipping-price-text">{{ format_price($shippingAmount) }}</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-6">
                                        <p><strong>{{ __('Total') }}</strong>:</p>
                                    </div>
                                    <div class="col-6 float-end">
                                        <p class="total-text raw-total-text"
                                            data-price="{{ format_price($rawTotal, null, true) }}"> {{ format_price($orderAmount) }} </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="mt-3 mb-5">
                            @include('plugins/ecommerce::themes.discounts.partials.form')
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6 left">
                        <div class="d-none d-sm-block">
                            @include('plugins/ecommerce::orders.partials.logo')
                        </div>
                        <div class="form-checkout">
                            {!! apply_filters('ecommerce_checkout_form_before', null, $products) !!}

                            @if ($isShowAddressForm)
                                <div>
                                    <h5 class="checkout-payment-title">{{ __('Shipping information') }}</h5>
                                    <input type="hidden" value="{{ route('public.checkout.save-information', $token) }}" id="save-shipping-information-url">
                                    @include('plugins/ecommerce::orders.partials.address-form', compact('sessionCheckoutData'))
                                </div>
                                <br>
                                {!! apply_filters('ecommerce_checkout_form_after_shipping_address_form', null, $products) !!}
                            @endif

                            @if (EcommerceHelper::isBillingAddressEnabled())
                                <div>
                                    <h5 class="checkout-payment-title">{{ __('Billing information') }}</h5>
                                    @include('plugins/ecommerce::orders.partials.billing-address-form', compact('sessionCheckoutData'))
                                </div>
                                <br>
                                {!! apply_filters('ecommerce_checkout_form_after_billing_address_form', null, $products) !!}
                            @endif

                            @if (! is_plugin_active('marketplace'))
                                @if (Arr::get($sessionCheckoutData, 'is_available_shipping', true))
                                    <div id="shipping-method-wrapper">
                                        <h5 class="checkout-payment-title">{{ __('Shipping method') }}</h5>
                                        <div class="shipping-info-loading" style="display: none;">
                                            <div class="shipping-info-loading-content">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </div>
                                        </div>
                                        @if (!empty($shipping))
                                            <div class="payment-checkout-form">
                                                <input type="hidden" name="shipping_option" value="{{ BaseHelper::stringify(old('shipping_option', $defaultShippingOption)) }}">
                                                <ul class="list-group list_payment_method">
                                                    @foreach ($shipping as $shippingKey => $shippingItems)
                                                        @foreach($shippingItems as $shippingOption => $shippingItem)
                                                            @include('plugins/ecommerce::orders.partials.shipping-option', [
                                                                'shippingItem' => $shippingItem,
                                                                'attributes' =>[
                                                                    'id' => 'shipping-method-' . $shippingKey . '-' . $shippingOption,
                                                                    'name' => 'shipping_method',
                                                                    'class' => 'magic-radio shipping_method_input',
                                                                    'checked' => old('shipping_method', $defaultShippingMethod) == $shippingKey && old('shipping_option', $defaultShippingOption) == $shippingOption,
                                                                    'disabled' => Arr::get($shippingItem, 'disabled'),
                                                                    'data-option' => $shippingOption,
                                                                ],
                                                            ])
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <p>{{ __('No shipping methods available!') }}</p>
                                        @endif
                                    </div>
                                    <br>
                                    {!! apply_filters('ecommerce_checkout_form_after_shipping_address_form', null, $products) !!}
                                @endif
                            @endif

                            {!! apply_filters('ecommerce_checkout_form_before_payment_form', null, $products) !!}

                            @if (is_plugin_active('payment'))
                                <div class="position-relative">
                                    <div class="payment-info-loading" style="display: none;">
                                        <div class="payment-info-loading-content">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </div>
                                    </div>
                                    <h5 class="checkout-payment-title">{{ __('Payment method') }}</h5>
                                    <input type="hidden" name="amount" value="{{ format_price($orderAmount, null, true) }}">
                                    <input type="hidden" name="currency" value="{{ strtoupper(get_application_currency()->title) }}">
                                    @if (is_plugin_active('payment'))
                                        {!! apply_filters(PAYMENT_FILTER_PAYMENT_PARAMETERS, null) !!}
                                    @endif
                                    <ul class="list-group list_payment_method">
                                        @if ($orderAmount)
                                            {!! apply_filters(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, null, [
                                                'amount' => format_price($orderAmount, null, true),
                                                'currency' => strtoupper(get_application_currency()->title),
                                                'name' => null,
                                                'selected' => PaymentMethods::getSelectedMethod(),
                                                'default' => PaymentMethods::getDefaultMethod(),
                                                'selecting' => PaymentMethods::getSelectingMethod(),
                                            ]) !!}

                                            {!! PaymentMethods::render() !!}
                                        @endif
                                    </ul>
                                </div>
                                <br>
                            @else
                                <input type="hidden" name="amount" value="{{ format_price($orderAmount, null, true) }}">
                            @endif

                            {!! apply_filters('ecommerce_checkout_form_after_payment_form', null, $products) !!}

                            <div class="form-group mb-3 @if ($errors->has('description')) has-error @endif">
                                <label for="description" class="control-label mb-2">{{ __('Order notes') }}</label>
                                <br>
                                <textarea name="description" id="description" rows="3" class="form-control" placeholder="{{ __('Notes about your order, e.g. special notes for delivery.') }}">{{ old('description') }}</textarea>
                                {!! Form::error('description', $errors) !!}
                            </div>

                            @if (EcommerceHelper::getMinimumOrderAmount() > Cart::instance('cart')->rawSubTotal())
                                <div class="alert alert-warning">
                                    {{ __('Minimum order amount is :amount, you need to buy more :more to place an order!', ['amount' => format_price(EcommerceHelper::getMinimumOrderAmount()), 'more' => format_price(EcommerceHelper::getMinimumOrderAmount() - Cart::instance('cart')->rawSubTotal())]) }}
                                </div>
                            @endif

                            @if(EcommerceHelper::isDisplayTaxFieldsAtCheckoutPage())
                                @include('plugins/ecommerce::orders.partials.tax-information', compact('sessionCheckoutData'))

                                {!! apply_filters('ecommerce_checkout_form_after_tax_information_form', null, $products) !!}
                            @endif

                            {!! apply_filters('ecommerce_checkout_form_after', null, $products) !!}

                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-md-6 d-none d-md-block" style="line-height: 53px">
                                        <a class="text-info" href="{{ route('public.cart') }}"><i class="fas fa-long-arrow-alt-left"></i> <span class="d-inline-block back-to-cart">{{ __('Back to cart') }}</span></a>
                                    </div>
                                    <div class="col-md-6 checkout-button-group">
                                        @if (EcommerceHelper::isValidToProcessCheckout())
                                            <button type="submit" class="btn payment-checkout-btn payment-checkout-btn-step float-end" data-processing-text="{{ __('Processing. Please wait...') }}" data-error-header="{{ __('Error') }}">
                                                {{ __('Checkout') }}
                                            </button>
                                        @else
                                            <span class="btn payment-checkout-btn-step float-end disabled">
                                                {{ __('Checkout') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-block d-md-none back-to-cart-button-group">
                                    <a class="text-info" href="{{ route('public.cart') }}">
                                        <i class="fas fa-long-arrow-alt-left"></i>
                                        <span class="d-inline-block">{{ __('Back to cart') }}</span>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}

        @if (is_plugin_active('payment'))
            @include('plugins/payment::partials.footer')
        @endif
    @else
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning my-5">
                        <span>{!! __('No products in cart. :link!', ['link' => Html::link(route('public.index'), __('Back to shopping'))]) !!}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@push('footer')
    <script type="text/javascript" src="{{ asset('vendor/core/core/js-validation/js/js-validation.js')}}"></script>
    {!! JsValidator::formRequest(\Botble\Ecommerce\Http\Requests\SaveCheckoutInformationRequest::class, '#checkout-form'); !!}
@endpush
