<span class="product-price">
    <span class="product-price-sale d-flex align-items-center @if (! $product->isOnSale()) d-none @endif">
        <del aria-hidden="true">
            <span class="price-amount">
                <bdi>
                    <span class="amount">{{ format_price($product->price_with_taxes) }}</span>
                </bdi>
            </span>
        </del>
        <ins>
            <span class="price-amount">
                <bdi>
                    <span class="amount">{{ format_price($product->front_sale_price_with_taxes) }}</span>
                </bdi>
            </span>
        </ins>
    </span>
    <span class="product-price-original @if ($product->isOnSale()) d-none @endif">
        <span class="price-amount">
            <bdi>
                <span class="amount">{{ format_price($product->front_sale_price_with_taxes) }}</span>
            </bdi>
        </span>
    </span>
</span>
