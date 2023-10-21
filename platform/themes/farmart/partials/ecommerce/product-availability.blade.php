<div class="summary-meta">
    @if ($product->isOutOfStock())
        <div class="product-stock out-of-stock d-inline-block">
            <label>{{ __('Availability') }}:</label>{{ __('Out of stock') }}
        </div>
    @elseif  (!$product->with_storehouse_management || $product->quantity < 1)
        <div class="product-stock in-stock d-inline-block">
            <label>{{ __('Availability') }}:</label> {!! BaseHelper::clean($product->stock_status_html) !!}
        </div>
    @elseif ($product->quantity)
        @if (EcommerceHelper::showNumberOfProductsInProductSingle())
            <div class="product-stock in-stock d-inline-block">
                <label>{{ __('Availability') }}:</label>
                @if ($product->quantity != 1)
                    {{ __(':number products available', ['number' => $product->quantity]) }}
                @else
                    {{ __(':number product available', ['number' => $product->quantity]) }}
                @endif
            </div>
        @else
            <div class="product-stock in-stock d-inline-block">
                <label>{{ __('Availability') }}:</label>{{ __('In stock') }}
            </div>
        @endif
    @endif
</div>
