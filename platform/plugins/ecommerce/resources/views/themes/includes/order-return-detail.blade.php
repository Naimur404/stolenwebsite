@if ($orderReturn)
    <div class="customer-order-detail">
        <div class="row">
            <div class="col-md-6">
                <h5>{{ __('Return Product(s) Information') }}</h5>
                <p>
                    <span>{{ __('Request number') }}: </span>
                    <strong>{{ $orderReturn->code }}</strong>
                </p>
                <p>
                    <span>{{ __('Order Id') }}: </span>
                    <strong>{{ $orderReturn->order->code }}</strong>
                </p>
            </div>
            <div class="col-md-6">
                <p>
                    <span>{{ __('Time') }}: </span>
                    <strong class="text-info">{{ $orderReturn->created_at->translatedFormat('h:m d/m/Y') }}</strong>
                </p>
                <p>
                    <span>{{ __('Status') }}: </span>
                    <strong class="text-warning">{{ $orderReturn->return_status->label() }}</strong>
                </p>
                @if (! EcommerceHelper::allowPartialReturn())
                    <p>
                        <span>{{ __('Reason') }}: </span>
                        <strong class="text-warning">{{ $orderReturn->reason->label() }}</strong>
                    </p>
                @endif
            </div>
        </div>
        <br/>
        <h5>{{ __('Return items') }}</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">{{ __('Image') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th class="text-center">{{ __('Quantity') }}</th>
                                <th class="text-center">{{ __('Refund amount') }}</th>
                                @if (EcommerceHelper::allowPartialReturn())
                                    <th class="text-center">{{ __('Reason') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderReturn->items as $item)
                                @php
                                    $orderProduct = $item->orderProduct;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <img src="{{ RvMedia::getImageUrl($item->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                            alt="{{ $item->product_name }}" width="50">
                                    </td>
                                    <td>
                                        {{ $item->product_name }}
                                        @if ($orderProduct)
                                            @if ($sku = Arr::get($orderProduct->options, 'sku'))
                                                ({{ $sku }})
                                            @endif
                                            @if ($attributes = Arr::get($orderProduct->options, 'attributes'))
                                                <p>
                                                    <small>{{ $attributes }}</small>
                                                </p>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-info">{{ number_format($item->qty) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-info">{{ format_price($item->refund_amount) }}</strong>
                                    </td>
                                    @if (EcommerceHelper::allowPartialReturn())
                                        <td class="text-center">
                                            <span class="text-warning">{{ $item->reason->label() }}</span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@else
    <p class="text-center text-danger">{{ __('Order Return Request not found!') }}</p>
@endif
