<div class="product-loop__buttons">
    <div class="quick-view-button product-loop_button product-quick-view-button">
        <a class="quick-view product-loop_action" href="#"
            data-url="{{ route('public.ajax.quick-view', ['product_id' => $product->id]) }}"
            title="{{ __('Quick view') }}" data-bs-toggle="tooltip">
            <div class="product-loop_icon">
                <span class="svg-icon">
                    <svg>
                        <use href="#svg-icon-quick-view" xlink:href="#svg-icon-quick-view"></use>
                    </svg>
                </span>
            </div>
            <span class="text">{{ __('Quick view') }}</span>
        </a>
    </div>
    @if (EcommerceHelper::isWishlistEnabled())
        <div class="wishlist-button product-wishlist-button product-loop_button">
            <a class="wishlist product-loop_action @if (!empty($wishlistIds) && in_array($product->id, $wishlistIds)) added-to-wishlist @endif" href="#"
                data-url="{{ route('public.ajax.add-to-wishlist', ['product_id' => $product->id]) }}" title="{{ __('Wishlist') }}">
                <div class="wishlist-icons product-loop_icon">
                    <span class="svg-icon">
                        <svg>
                            <use href="#svg-icon-wishlist" xlink:href="#svg-icon-wishlist"></use>
                        </svg>
                    </span>
                    <span class="svg-icon">
                        <svg>
                            <use href="#svg-icon-wishlisted" xlink:href="#svg-icon-wishlisted"></use>
                        </svg>
                    </span>
                </div>
                <span class="text">{{ __('Wishlist') }}</span>
            </a>
        </div>
    @endif
    @if (EcommerceHelper::isCompareEnabled())
        <div class="compare-button product-compare-button product-loop_button">
            <a class="compare product-loop_action" href="#" data-url="{{ route('public.compare.add', $product->id) }}" title="{{ __('Compare') }}">
                <div class="compare-icons product-loop_icon">
                    <span class="svg-icon">
                        <svg>
                            <use href="#svg-icon-compare" xlink:href="#svg-icon-compare"></use>
                        </svg>
                    </span>
                </div>
                <span class="text">{{ __('Compare') }}</span>
            </a>
        </div>
    @endif
</div>
