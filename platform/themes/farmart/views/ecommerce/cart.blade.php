<div class="row cart-page-content py-5 mt-3">
    <div class="col-12">
        <form class="form--shopping-cart cart-form" method="post" action="{{ route('public.cart.update') }}">
            @csrf
            @if (count($products) > 0)
                <table class="table cart-form__contents" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="product-thumbnail"></th>
                            <th class="product-name">{{ __('Product') }}</th>
                            <th class="product-price product-md d-md-table-cell d-none">{{ __('Price') }}</th>
                            <th class="product-quantity product-md d-md-table-cell d-none">{{ __('Quantity') }}</th>
                            <th class="product-subtotal product-md d-md-table-cell d-none">{{ __('Total') }}</th>
                            <th class="product-remove"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (Cart::instance('cart')->content() as $key => $cartItem)
                            @php
                                $product = $products->find($cartItem->id);
                            @endphp

                            @if (!empty($product))
                                <tr class="cart-form__cart-item cart_item">
                                    <td class="product-thumbnail">
                                        <input type="hidden" name="items[{{ $key }}][rowId]" value="{{ $cartItem->rowId }}">

                                        <a href="{{ $product->original_product->url }}"
                                            style="max-width: 74px; display: inline-block;">
                                            <img class="lazyload" src="{{ image_placeholder(RvMedia::getImageUrl($cartItem->options['image'], 'thumb', false, RvMedia::getDefaultImage())) }}" data-src="{{ RvMedia::getImageUrl($cartItem->options['image'], 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                alt="{{ $product->original_product->name }}">
                                        </a>
                                    </td>
                                    <td class="product-name d-md-table-cell d-block" data-title="{{ __('Product') }}">
                                        <a href="{{ $product->original_product->url }}">{{ $product->original_product->name }}</a>
                                        @if (is_plugin_active('marketplace') && $product->original_product->store->id)
                                            <div class="variation-group">
                                                <span class="text-secondary">{{ __('Vendor') }}: </span>
                                                <span class="text-primary ms-1">
                                                    <a href="{{ $product->original_product->store->url }}">{{ $product->original_product->store->name }}</a>
                                                </span>
                                            </div>
                                        @endif
                                        @if ($attributes = Arr::get($cartItem->options, 'attributes'))
                                            <p class="mb-0">
                                                <small>{{ $attributes }}</small>
                                            </p>
                                        @endif
                                        @if (EcommerceHelper::isEnabledProductOptions() && ! empty($cartItem->options['options']))
                                            {!! render_product_options_html($cartItem->options['options'], $product->original_product->front_sale_price_with_taxes) !!}
                                        @endif

                                        @include('plugins/ecommerce::themes.includes.cart-item-options-extras', ['options' => $cartItem->options])
                                    </td>
                                    <td class="product-price product-md d-md-table-cell d-block" data-title="Price">
                                        <div class="box-price">
                                            <span class="d-md-none title-price">{{ __('Price') }}: </span>
                                            <span class="quantity">
                                                <span class="price-amount amount">
                                                    <bdi>{{ format_price($cartItem->price) }} @if ($product->front_sale_price != $product->price)
                                                            <small><del>{{ format_price($product->price) }}</del></small>
                                                        @endif</bdi>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="product-quantity product-md d-md-table-cell d-block" data-title="{{ __('Quantity') }}">
                                        <div class="product-button">
                                            {!! Theme::partial('ecommerce.product-quantity', compact('product') + [
                                                    'name' => 'items[' . $key . '][values][qty]',
                                                    'value' => $cartItem->qty,
                                                ]) !!}
                                        </div>
                                    </td>
                                    <td class="product-subtotal product-md d-md-table-cell d-block" data-title="{{ __('Total') }}">
                                        <div class="box-price">
                                            <span class="d-md-none title-price">{{ __('Total') }}: </span>
                                            <span class="fw-bold amount">
                                                <span class="price-current">{{ format_price($cartItem->price * $cartItem->qty) }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="product-remove">
                                        <a class="fs-4 remove btn remove-cart-item" href="#"
                                            data-url="{{ route('public.cart.remove', $cartItem->rowId) }}"
                                            aria-label="Remove this item">
                                            <span class="svg-icon">
                                                <svg>
                                                    <use href="#svg-icon-trash" xlink:href="#svg-icon-trash"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center">{{ __('Your cart is empty!') }}</p>
            @endif
            @if (count($products) > 0)
                <div class="actions my-4 pb-4 border-bottom">
                    <div class="actions__button-wrapper row justify-content-between">
                        <div class="col-md-9">
                            <div class="actions__left d-grid d-md-block">
                                <a class="btn btn-secondary mb-2" href="{{ route('public.products') }}">
                                    <span class="svg-icon">
                                        <svg>
                                            <use href="#svg-icon-arrow-left" xlink:href="#svg-icon-arrow-left"></use>
                                        </svg>
                                    </span> {{ __('Continue Shopping') }}
                                </a>
                                <a class="btn btn-secondary mb-2 ms-md-2" href="{{ route('public.index') }}">
                                    <span class="svg-icon">
                                        <svg>
                                            <use href="#svg-icon-home" xlink:href="#svg-icon-home"></use>
                                        </svg>
                                    </span> {{ __('Back to Home') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-lg-4 col-md-5 col-coupon form-coupon-wrapper">
                        <div class="coupon">
                            <label for="coupon_code">
                                <h4>{{ __('Using A Promo Code?') }}</h4>
                            </label>
                            <div class="coupon-input input-group my-3">
                                <input class="form-control coupon-code" type="text" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="{{ __('Enter coupon code') }}">
                                <button class="btn btn-primary lh-1 btn-apply-coupon-code" type="button" data-url="{{ route('public.coupon.apply') }}">{{ __('Apply') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-2"></div>
                    <div class="col-lg-4 col-md-5">
                        <div class="cart_totals bg-light p-4 rounded">
                            <h5 class="mb-3">{{ __('Cart totals') }}</h5>
                            <div class="cart_totals-table">
                                <div class="cart-subtotal d-flex justify-content-between border-bottom pb-3 mb-3">
                                    <span class="title fw-bold">{{ __('Subtotal') }}:</span>
                                    <span class="amount fw-bold">
                                        <span class="price-current">{{ format_price(Cart::instance('cart')->rawSubTotal()) }}</span>
                                    </span>
                                </div>
                                @if (EcommerceHelper::isTaxEnabled())
                                    <div class="cart-subtotal d-flex justify-content-between border-bottom pb-3 mb-3">
                                        <span class="title fw-bold">{{ __('Tax') }}:</span>
                                        <span class="amount fw-bold">
                                            <span class="price-current">{{ format_price(Cart::instance('cart')->rawTax()) }}</span>
                                        </span>
                                    </div>
                                @endif
                                @if ($couponDiscountAmount > 0 && session('applied_coupon_code'))
                                    <div class="cart-subtotal d-flex justify-content-between border-bottom pb-3 mb-3">
                                        <span class="title">
                                            <span class="fw-bold">{{ __('Coupon code: :code', ['code' => session('applied_coupon_code')]) }}</span>
                                            (<small>
                                                <a class="btn-remove-coupon-code text-danger" data-url="{{ route('public.coupon.remove') }}"
                                                href="#" data-processing-text="{{ __('Removing...') }}">{{ __('Remove') }}</a>
                                            </small>)
                                        </span>

                                        <span class="amount fw-bold">{{ format_price($couponDiscountAmount) }}</span>
                                    </div>
                                @endif
                                @if ($promotionDiscountAmount)
                                    <div class="ps-block__header">
                                        <p>{{ __('Discount promotion') }} <span> {{ format_price($promotionDiscountAmount) }}</span></p>
                                    </div>
                                @endif
                                <div class="order-total d-flex justify-content-between pb-3 mb-3">
                                    <span class="title">
                                        <h6 class="mb-0">{{ __('Total') }}</h6>
                                        <small>{{ __('(Shipping fees not included)') }}</small>
                                    </span>
                                    <span class="amount fw-bold fs-6 text-green">
                                        <span class="price-current">{{ ($promotionDiscountAmount + $couponDiscountAmount) > Cart::instance('cart')->rawTotal() ? format_price(0) : format_price(Cart::instance('cart')->rawTotal() - $promotionDiscountAmount - $couponDiscountAmount) }}</span>
                                    </span>
                                </div>
                            </div>
                            @if (session('tracked_start_checkout'))
                                <div class="proceed-to-checkout">
                                    <div class="d-grid gap-2">
                                        <a class="checkout-button btn btn-primary" href="{{ route('public.checkout.information', session('tracked_start_checkout')) }}">{{ __('Proceed to checkout') }}</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </form>

        @if (count($crossSellProducts) > 0)
            <div class="row align-items-center mb-2 widget-header">
                <h2 class="col-auto mb-0 py-2">{{ __('Customers who bought this item also bought') }}</h2>
            </div>
            <div class="row row-cols-lg-6 row-cols-md-4 row-cols-3 g-0 products-with-border">
                @foreach($crossSellProducts as $crossSellProduct)
                    <div class="col">
                        <div class="product-inner">
                            {!! Theme::partial('ecommerce.product-item', ['product' => $crossSellProduct]) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
