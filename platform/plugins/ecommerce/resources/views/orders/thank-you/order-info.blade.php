<div class="pt-3 mb-4">
    <div class="align-items-center">
        <h6 class="d-inline-block">{{ __('Order number') }}: {{ $order->code }}</h6>
    </div>

    <div class="checkout-success-products">
        <div class="row show-cart-row d-md-none p-2">
            <div class="col-9">
                <a class="show-cart-link"
                   href="javascript:void(0);"
                   data-bs-toggle="collapse"
                   data-bs-target="{{ '#cart-item-' . $order->id }}">
                    {{ __('Order information :order_id', ['order_id' => $order->code]) }} <i class="fa fa-angle-down" aria-hidden="true"></i>
                </a>
            </div>
            <div class="col-3">
                <p class="text-end mobile-total"> {{ format_price($order->amount) }} </p>
            </div>
        </div>
        <div id="{{ 'cart-item-' . $order->id }}" class="collapse collapse-products">
            @foreach ($order->products as $orderProduct)
                <div class="row cart-item">
                    <div class="col-lg-3 col-md-3">
                        <div class="checkout-product-img-wrapper">
                            <img class="item-thumb img-thumbnail img-rounded"
                                src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                alt="{{ $orderProduct->product_name }}">
                            <span class="checkout-quantity">{{ $orderProduct->qty }}</span>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-5">
                        <p class="mb-0">{{ $orderProduct->product_name }}</p>
                        <p class="mb-0">
                            <small>{{ Arr::get($orderProduct->options, 'attributes', '') }}</small>
                        </p>
                        @if (! empty($orderProduct->product_options) && is_array($orderProduct->product_options))
                            {!! render_product_options_html($orderProduct->product_options, $orderProduct->price) !!}
                        @endif

                        @include('plugins/ecommerce::themes.includes.cart-item-options-extras', ['options' => $orderProduct->options])
                    </div>
                    <div class="col-lg-4 col-md-4 col-4 float-end text-end">
                        <p>{{ format_price($orderProduct->price) }}</p>
                    </div>
                </div>
            @endforeach

            @if (! empty($isShowTotalInfo))
                @include('plugins/ecommerce::orders.thank-you.total-info', compact('order'))
            @endif
        </div>
    </div>
</div>
