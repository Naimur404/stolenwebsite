<div class="row wishlist-page-content py-5 mt-3">
    <div class="col-12">
        @if ($products->total() && $products->loadMissing(['options', 'options.values']))
            <table class="table cart-form__contents" cellspacing="0">
                <thead>
                    <tr>
                        <th class="product-thumbnail"></th>
                        <th class="product-name">{{ __('Product') }}</th>
                        <th class="product-price product-md d-md-table-cell d-none">{{ __('Unit Price') }}</th>
                        <th class="product-quantity product-md d-md-table-cell d-none">{{ __('Stock status') }}</th>
                        @if (EcommerceHelper::isCartEnabled())
                            <th class="product-subtotal product-md d-md-table-cell d-none"></th>
                        @endif
                        <th class="product-remove"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="cart-form__cart-item cart_item">
                            <td class="product-thumbnail">
                                <a class="img-fluid-eq" href="{{ $product->original_product->url }}">
                                    <div class="img-fluid-eq__dummy"></div>
                                    <div class="img-fluid-eq__wrap">
                                        <img class="lazyload" src="{{ image_placeholder($product->image, 'thumb') }}"
                                            data-src="{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}">
                                    </div>
                                </a>
                            </td>
                            <td class="product-name d-md-table-cell d-block" data-title="Product">
                                <a href="{{ $product->original_product->url }}">{{ $product->name }}</a>
                                @if (is_plugin_active('marketplace') && $product->original_product->store->id)
                                    <div class="variation-group">
                                        <span class="text-secondary">{{ __('Vendor') }}:</span>
                                        <span class="text-primary ms-1">
                                            <a href="{{ $product->original_product->store->url }}">{{ $product->original_product->store->name }}</a>
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="product-price product-md d-md-table-cell d-block" data-title="Price">
                                <div class="box-price">
                                    <span class="d-md-none title-price">{{ __('Price') }}: </span>
                                    {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                                </div>
                            </td>
                            <td class="product-quantity product-md d-md-table-cell d-block" data-title="Stock status">
                                <div class="without-bg product-stock @if ($product->isOutOfStock()) out-of-stock @else in-stock @endif">
                                    @if ($product->isOutOfStock()) {{ __('Out Of Stock') }} @else {{ __('In Stock') }} @endif
                                </div>
                            </td>
                            @if (EcommerceHelper::isCartEnabled())
                                <td class="product-subtotal product-md d-md-table-cell d-block" data-title="Total">
                                    {!! Theme::partial('ecommerce.product-cart-form', compact('product')) !!}
                                </td>
                            @endif
                            <td class="product-remove">
                                <button type="button" class="fs-4 remove btn remove-wishlist-item" href="#"
                                    data-url="{{ route('public.wishlist.remove', $product->id) }}"
                                    aria-label="{{ __('Remove this item') }}">
                                    <span class="svg-icon">
                                        <svg>
                                            <use href="#svg-icon-trash" xlink:href="#svg-icon-trash"></use>
                                        </svg>
                                    </span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">
                {!! $products->links() !!}
            </div>
        @else
            <p class="text-center">{{ __('No products in wishlist!') }}</p>
        @endif
    </div>

</div>
