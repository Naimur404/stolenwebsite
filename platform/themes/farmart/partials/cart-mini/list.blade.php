<div class="panel__header">
    <span class="svg-icon close-toggle--sidebar" data-toggle-closest=".cart__content">
        <svg>
            <use href="#svg-icon-arrow-left" xlink:href="#svg-icon-arrow-left"></use>
        </svg>
    </span>
    <h3>{{ __('Cart') }} <span class="cart-counter">({{ Cart::instance('cart')->count() }})</span></h3>
</div>
<div class="cart__items">
    @if (Cart::instance('cart')->count() > 0 && ($products = Cart::instance('cart')->products()) && $products->count() > 0)
        <ul class="mini-product-cart-list">
            @foreach(Cart::instance('cart')->content() as $key => $cartItem)
                @if ($product = $products->find($cartItem->id))
                    {!! Theme::partial('cart-mini.item', compact('product', 'cartItem')) !!}
                @endif
            @endforeach
        </ul>
    @else
        <div class="cart_no_items py-3 px-3">
            <span class="cart-empty-message">{{ __('No products in the cart.') }}</span>
        </div>
    @endif
</div>

@if (Cart::instance('cart')->count() > 0 && Cart::instance('cart')->products()->count() > 0)
    <div class="control-buttons">
        @if (EcommerceHelper::isTaxEnabled())
            <div class="mini-cart__total">
                <strong>{{ __('Sub Total') }}:</strong>
                <span class="price-amount">
                    <bdi>{{ format_price(Cart::instance('cart')->rawSubTotal()) }}</bdi>
                </span>
            </div>
            <div class="mini-cart__total">
                <strong>{{ __('Tax') }}:</strong>
                <span class="price-amount">
                    <bdi>{{ format_price(Cart::instance('cart')->rawTax()) }}</bdi>
                </span>
            </div>
            <div class="mini-cart__total">
                <strong class="text-uppercase">{{ __('Total') }}:</strong>
                <span class="price-amount">
                    <bdi>{{ format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()) }}</bdi>
                </span>
            </div>
        @else
            <div class="mini-cart__total">
                <strong class="text-uppercase">{{ __('Sub Total') }}:</strong>
                <span class="price-amount">
                    <bdi>
                        {{ format_price(Cart::instance('cart')->rawSubTotal()) }}
                    </bdi>
                </span>
            </div>
        @endif
        <div class="mini-cart__buttons row g-0">
            <div class="col me-2">
                <a class="btn btn-light" href="{{ route('public.cart') }}">{{ __('View Cart') }}</a></div>
            <div class="col ms-2">
                @if (session('tracked_start_checkout'))
                    <a class="btn btn-primary checkout"
                        href="{{ route('public.checkout.information', session('tracked_start_checkout')) }}">{{ __('Checkout') }}</a>
                @endif
            </div>
        </div>
    </div>
@endif
