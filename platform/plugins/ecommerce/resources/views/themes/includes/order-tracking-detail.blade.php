@if ($order)
    <div class="customer-order-detail">
        <div class="row">
            <div class="col-md-6">
                <h5>{{ __('Order information') }}</h5>
                <p>
                    <span>{{ __('Order number') }}: </span>
                    <strong>{{ $order->code }}</strong>
                </p>
                <p>
                    <span>{{ __('Time') }}: </span>
                    <strong>{{ $order->created_at->translatedFormat('h:m d/m/Y') }}</strong>
                </p>
                <p>
                    <span>{{ __('Order status') }}: </span>
                    <strong class="text-info">{{ $order->status->label() }}</strong>
                </p>
                @if (is_plugin_active('payment'))
                    <p>
                        <span>{{ __('Payment method') }}: </span>
                        <strong class="text-info">{{ $order->payment->payment_channel->label() }}</strong>
                    </p>
                    <p>
                        <span>{{ __('Payment status') }}: </span>
                        <strong class="text-info">{{ $order->payment->status->label() }}</strong>
                    </p>
                @endif
                @if ($order->description)
                    <p>
                        <span>{{ __('Note') }}: </span>
                        <strong class="text-warning"><i>{{ $order->description }}</i></strong>
                    </p>
                @endif
            </div>
            @if ($order->address->name)
                <div class="col-md-6">
                    <h5>{{ __('Customer information') }}</h5>
                    <p>
                        <span>{{ __('Full Name') }}: </span>
                        <strong>{{ $order->address->name }}</strong>
                    </p>
                    <p>
                        <span>{{ __('Phone') }}: </span>
                        <strong>{{ $order->address->phone }}</strong>
                    </p>
                    <p>
                        <span>{{ __('Address') }}: </span>
                        <strong> {{ $order->address->address }}</strong>
                    </p>
                    <p>
                        <span>{{ __('City') }}: </span>
                        <strong>{{ $order->address->city_name }}</strong>
                    </p>
                    <p>
                        <span>{{ __('State') }}: </span>
                        <strong> {{ $order->address->state_name }}</strong>
                    </p>
                    <p>
                        <span>{{ __('Country') }}: </span>
                        <strong> {{ $order->address->country_name }}</strong>
                    </p>
                    @if (EcommerceHelper::isZipCodeEnabled())
                        <p>
                            <span>{{ __('Zip code') }}: </span>
                            <strong> {{ $order->address->zip_code }}</strong>
                        </p>
                    @endif
                </div>
            @endif
        </div>
        <br>
        <h5>{{ __('Order detail') }}</h5>
        <div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">{{ __('Image') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th class="text-center">{{ __('Amount') }}</th>
                            <th class="text-end" style="width: 100px">{{ __('Quantity') }}</th>
                            <th class="price text-end">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->products as $orderProduct)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    <img src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                        width="50" alt="{{ $orderProduct->product_name }}">
                                </td>
                                <td>
                                    {{ $orderProduct->product_name }}
                                    @if ($sku = Arr::get($orderProduct->options, 'sku'))
                                        ({{ $sku }})
                                    @endif
                                    @if ($attributes = Arr::get($orderProduct->options, 'attributes'))
                                        <p class="mb-0">
                                            <small>{{ $attributes }}</small>
                                        </p>
                                    @endif

                                    @include('plugins/ecommerce::themes.includes.cart-item-options-extras', ['options' => $orderProduct->options])

                                    @if (! empty($orderProduct->product_options) && is_array($orderProduct->product_options))
                                        {!! render_product_options_html($orderProduct->product_options, $orderProduct->price) !!}
                                    @endif
                                </td>
                                <td class="text-center">{{ format_price($orderProduct->price_with_tax) }}</td>
                                <td class="text-center">{{ $orderProduct->qty }}</td>
                                <td class="money text-end">
                                    <strong>
                                        {{ format_price($orderProduct->total_price_with_tax) }}
                                    </strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p>
                <span>{{ __('Shipping fee') }}: </span>
                <strong>{{ format_price($order->shipping_amount) }}</strong>
            </p>
            <p>
                <span>{{ __('Total Amount') }}: </span>
                <strong>{{ format_price($order->amount) }}</strong>
            </p>
        </div>

        @if ($order->shipment->id)
            <br>
            <h5>{{ __('Shipping Information') }}: </h5>
            <p>
                <span class="d-inline-block">{{ __('Shipping Status') }}: </span>
                <strong
                    class="d-inline-block text-info">{!! BaseHelper::clean($order->shipment->status->toHtml()) !!}</strong>
            </p>
            @if ($order->shipment->shipping_company_name)
                <p>
                    <span class="d-inline-block">{{ __('Shipping Company Name') }}: </span>
                    <strong class="d-inline-block">{{ $order->shipment->shipping_company_name }}</strong>
                </p>
            @endif
            @if ($order->shipment->tracking_id)
                <p>
                    <span class="d-inline-block">{{ __('Tracking ID') }}: </span>
                    <strong class="d-inline-block">{{ $order->shipment->tracking_id }}</strong>
                </p>
            @endif
            @if ($order->shipment->tracking_link)
                <p>
                    <span class="d-inline-block">{{ __('Tracking Link') }}: </span>
                    <strong class="d-inline-block">
                        <a href="{{ $order->shipment->tracking_link }}"
                           target="_blank">{{ $order->shipment->tracking_link }}</a>
                    </strong>
                </p>
            @endif
            @if ($order->shipment->note)
                <p>
                    <span class="d-inline-block">{{ __('Delivery Notes') }}: </span>
                    <strong class="d-inline-block">{{ $order->shipment->note }}</strong>
                </p>
            @endif
            @if ($order->shipment->estimate_date_shipped)
                <p>
                    <span class="d-inline-block">{{ __('Estimate Date Shipped') }}: </span>
                    <strong class="d-inline-block">{{ $order->shipment->estimate_date_shipped }}</strong>
                </p>
            @endif
            @if ($order->shipment->date_shipped)
                <p>
                    <span class="d-inline-block">{{ __('Date Shipped') }}: </span>
                    <strong class="d-inline-block">{{ $order->shipment->date_shipped }}</strong>
                </p>
            @endif
        @endif
    </div>
@elseif (request()->input('order_id') || request()->input('email'))
    <p class="text-center text-danger">{{ __('Order not found!') }}</p>
@endif
