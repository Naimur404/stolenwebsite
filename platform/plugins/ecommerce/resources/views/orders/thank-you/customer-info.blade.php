@php
    $orders = $order;
    $order = $order instanceof \Illuminate\Support\Collection ? $order->first() : $order;
@endphp

<div class="order-customer-info">
    <h3> {{ __('Customer information') }}</h3>
    @if ($order->address->id)
        @if ($order->address->name)
            <p>
                <span class="d-inline-block">{{ __('Full name') }}:</span>
                <span class="order-customer-info-meta">{{ $order->address->name }}</span>
            </p>
        @endif

        @if ($order->address->phone)
            <p>
                <span class="d-inline-block">{{ __('Phone') }}:</span>
                <span class="order-customer-info-meta">{{ $order->address->phone }}</span>
            </p>
        @endif

        @if ($order->address->email)
            <p>
                <span class="d-inline-block">{{ __('Email') }}:</span>
                <span class="order-customer-info-meta">{{ $order->address->email }}</span>
            </p>
        @endif

        @if ($order->full_address)
            <p>
                <span class="d-inline-block">{{ __('Address') }}:</span>
                <span class="order-customer-info-meta">{{ $order->full_address }}</span>
            </p>
        @endif
    @endif

    @if (!empty($isShowShipping))
        <p>
            <span class="d-inline-block">{{ __('Shipping method') }}:</span>
            <span class="order-customer-info-meta">{{ $order->shipping_method_name }} - {{ format_price($order->shipping_amount) }}</span>
        </p>
    @endif

    @if (is_plugin_active('payment'))
        <p>
            <span class="d-inline-block">{{ __('Payment method') }}:</span>
            <span class="order-customer-info-meta">{{ $order->payment->payment_channel->label() }}</span>
        </p>
        <p>
            <span class="d-inline-block">{{ __('Payment status') }}:</span>
            <span class="order-customer-info-meta" style="text-transform: uppercase">{!! $order->payment->status->toHtml() !!}</span>
        </p>

        @if(setting('payment_bank_transfer_display_bank_info_at_the_checkout_success_page', false) && $bankInfo = OrderHelper::getOrderBankInfo($orders))
            {!! $bankInfo !!}
        @endif
    @endif

    {!! apply_filters('ecommerce_thank_you_customer_info', null, $order) !!}
</div>

@if($tax = $order->taxInformation)
    <div class="order-customer-info">
        <h3> {{ __('Tax information') }}</h3>
        <p>
            <span class="d-inline-block">{{ __('Company name') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_name }}</span>
        </p>

        <p>
            <span class="d-inline-block">{{ __('Company tax code') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_tax_code }}</span>
        </p>

        <p>
            <span class="d-inline-block">{{ __('Company email') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_email }}</span>
        </p>

        <p>
            <span class="d-inline-block">{{ __('Company address') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_address }}</span>
        </p>
    </div>
@endif
