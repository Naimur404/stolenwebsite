<form class="cart-form" action="{{ route('public.cart.add-to-cart') }}" method="POST">
    @csrf
    @if (!empty($withVariations) && $product->variations()->count() > 0)
        <div class="pr_switch_wrap">
            {!! render_product_swatches($product, [
                'selected' => $selectedAttrs,
                'view'     => Theme::getThemeNamespace() . '::views.ecommerce.attributes.swatches-renderer'
            ]) !!}
        </div>
        <div class="number-items-available" style="display: none; margin-bottom: 10px;"></div>
    @endif

    @if (isset($withProductOptions) && $withProductOptions)
        {!! render_product_options($product) !!}
    @endif

    <input type="hidden"
        name="id" class="hidden-product-id"
        value="{{ ($product->is_variation || ! $product->defaultVariation->product_id) ? $product->id : $product->defaultVariation->product_id }}"/>

    @if (EcommerceHelper::isCartEnabled() || ! empty($withButtons))
        {!! apply_filters(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, null, $product) !!}
        <div class="product-button">
            @if (EcommerceHelper::isCartEnabled())
                {!! Theme::partial('ecommerce.product-quantity', compact('product')) !!}
                <button type="submit" name="add_to_cart" value="1" class="btn btn-primary mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif" @if ($product->isOutOfStock()) disabled @endif title="{{ __('Add to cart') }}">
                    <span class="svg-icon">
                        <svg>
                            <use href="#svg-icon-cart" xlink:href="#svg-icon-cart"></use>
                        </svg>
                    </span>
                    <span class="add-to-cart-text ms-2">{{ __('Add to cart') }}</span>
                </button>

                @if (EcommerceHelper::isQuickBuyButtonEnabled() && isset($withBuyNow) && $withBuyNow)
                    <button type="submit" name="checkout" value="1" class="btn btn-primary btn-black mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif" @if ($product->isOutOfStock()) disabled @endif title="{{ __('Buy Now') }}">
                        <span class="add-to-cart-text ms-2">{{ __('Buy Now') }}</span>
                    </button>
                @endif
            @endif
            @if (!empty($withButtons))
                {!! Theme::partial('ecommerce.product-loop-buttons', compact('product', 'wishlistIds')) !!}
            @endif
        </div>
    @endif
</form>
