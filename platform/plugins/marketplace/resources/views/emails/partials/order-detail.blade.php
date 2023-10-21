@foreach ($orders as $order)
    @if ($order->store)
        <div>
            <span>
                @lang('plugins/marketplace::store.forms.store') : {{ $order->store->name }}
            </span>
        </div>
    @endif
    @include('plugins/ecommerce::emails.partials.order-detail')
@endforeach

