<div class="product-gallery product-gallery--with-images row">
    <div class="product-gallery__wrapper">
        @forelse ($productImages as $img)
            <div class="product-gallery__image item">
                <a class="img-fluid-eq" href="{{ RvMedia::getImageUrl($img) }}">
                    <div class="img-fluid-eq__dummy"></div>
                    <div class="img-fluid-eq__wrap">
                        <img class="mx-auto" title="{{ $product->name }}" src="{{ image_placeholder($img) }}" data-lazy="{{ RvMedia::getImageUrl($img) }}">
                    </div>
                </a>
            </div>
        @empty
            <div class="product-gallery__image item">
                <a class="img-fluid-eq" href="{{ image_placeholder() }}">
                    <div class="img-fluid-eq__dummy"></div>
                    <div class="img-fluid-eq__wrap">
                        <img class="mx-auto" title="{{ $product->name }}" src="{{ image_placeholder() }}">
                    </div>
                </a>
            </div>
        @endforelse
    </div>
    <div class="product-gallery__variants px-1">
        @forelse ($productImages as $img)
            <div class="item">
                <div class="border p-1 m-1">
                    <img class="lazyload" title="{{ $product->name }}" src="{{ image_placeholder($img, 'thumb') }}" data-src="{{ RvMedia::getImageUrl($img, 'thumb') }}">
                </div>
            </div>
        @empty
            <div class="item">
                <div class="border p-1 m-1">
                    <img title="{{ $product->name }}" src="{{ image_placeholder() }}">
                </div>
            </div>
        @endforelse
    </div>
</div>
