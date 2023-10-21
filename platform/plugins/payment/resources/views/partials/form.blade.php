@include('plugins/payment::partials.header')

<div class="checkout-wrapper">
    <div>
        <form action="{{ $action }}" method="post" class="payment-checkout-form">
            @csrf
            <input type="hidden" name="name" value="{{ $name }}">
            <input type="hidden" name="amount" value="{{ $amount }}">
            <input type="hidden" name="currency" value="{{ $currency }}">
            @if (isset($returnUrl))
                <input type="hidden" name="return_url" value="{{ $returnUrl }}">
            @endif
            @if (isset($callbackUrl))
                <input type="hidden" name="callback_url" value="{{ $callbackUrl }}">
            @endif

            {!! apply_filters(PAYMENT_FILTER_PAYMENT_PARAMETERS, null) !!}

            @include('plugins/payment::partials.payment-methods')

            {!! apply_filters(PAYMENT_FILTER_AFTER_PAYMENT_METHOD, null) !!}

            <br>
            <div class="text-center">
                <button class="payment-checkout-btn btn btn-info" data-processing-text="{{ __('Processing. Please wait...') }}" data-error-header="{{ __('Error') }}">{{ __('Checkout') }}</button>
            </div>
        </form>
    </div>
</div>

@include('plugins/payment::partials.footer')
