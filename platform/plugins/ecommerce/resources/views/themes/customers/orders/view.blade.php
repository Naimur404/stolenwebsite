@extends(EcommerceHelper::viewPath('customers.master'))
@section('content')
    <h2 class="customer-page-title">{{ __('Order information') }}</h2>
    <div class="clearfix"></div>
    <br>

    <div class="customer-order-detail">

        <div class="row">
            <div class="col-md-6">
                <div class="order-slogan">
                    @if ($logo = theme_option('logo_in_the_checkout_page') ?: theme_option('logo'))
                        <img width="100" src="{{ RvMedia::getImageUrl($logo) }}"
                             alt="{{ theme_option('site_title') }}">
                        <br/>
                    @endif
                    {{ setting('contact_address') }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="order-meta">
                    <p>
                        <span>{{ __('Order number') }}:</span>
                        <span class="order-detail-value">{{ $order->code }}</span>
                    </p>
                    <span>{{ __('Time') }}:</span>
                    <span class="order-detail-value">{{ $order->created_at->format('h:m d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="row">
            @include('plugins/ecommerce::themes.includes.order-tracking-detail')
            <br>
            <div class="col-md-12">
                @if ($order->isInvoiceAvailable())
                    <a href="{{ route('customer.print-order', $order->id) }}?type=print" class="btn-print" target="_blank">{{ __('Print invoice') }}</a>&nbsp;
                    <a href="{{ route('customer.print-order', $order->id) }}" class="btn-print">{{ __('Download invoice') }}</a>
                @endif
                @if ($order->canBeCanceled())
                    <a href="{{ route('customer.orders.cancel', $order->id) }}"
                       class="btn-print">{{ __('Cancel order') }}</a>
                @endif
                @if ($order->canBeReturned())
                    <a href="{{ route('customer.orders.return', $order->id) }}"
                       class="btn-print">{{ __('Return Product(s)') }}</a>
                @endif
            </div>
        </div>
@endsection
