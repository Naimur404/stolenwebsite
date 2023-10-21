@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    @php Theme::set('pageName', __('Order information')) @endphp
    <div class="card">
        <div class="card-header">
            <h3>{{ __('Order information') }}</h3>
        </div>
        <div class="card-body">
            <div class="customer-order-detail">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="order-slogan">
                            @if ($logo = theme_option('logo_in_the_checkout_page') ?: theme_option('logo'))
                                <img width="100" src="{{ RvMedia::getImageUrl($logo) }}" alt="{{ theme_option('site_title') }}">
                                <br/>
                            @endif
                            {{ setting('contact_address') }}
                        </div>
                    </div>
                </div>
                @include('plugins/ecommerce::themes.includes.order-tracking-detail')
                <br>
                <div>
                    @if ($order->isInvoiceAvailable())
                        <a href="{{ route('customer.print-order', $order->id) }}" class="btn btn-small btn-secondary mr-2">
                            <i class="fa fa-download"></i> {{ __('Download invoice') }}
                        </a>
                    @endif

                    @if ($order->canBeCanceled())
                        <a href="{{ route('customer.orders.cancel', $order->id) }}" onclick="return confirm('{{ __('Are you sure?') }}')" class="btn btn-lg btn-danger">
                            {{ __('Cancel order') }}
                        </a>
                    @endif

                    @if ($order->canBeReturned())
                            <a href="{{ route('customer.order_returns.request_view', $order->id) }}"
                               class="btn btn-lg btn-danger">
                                {{ __('Return Product(s)') }}
                            </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
