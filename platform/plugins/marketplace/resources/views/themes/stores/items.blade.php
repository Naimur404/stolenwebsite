<div class="ps-shopping-product">
    @if ($products->count() > 0)
        @foreach($products as $product)
            <div class="ps-product ps-product--wide">
                {!! Theme::partial('product-item-grid', compact('product')) !!}
            </div>
        @endforeach
    @endif
</div>
<div class="ps-pagination">
    {!! $products->withQueryString()->links() !!}
</div>
