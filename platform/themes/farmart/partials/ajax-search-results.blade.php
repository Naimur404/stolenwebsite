@if ($products->count())
    <div class="panel__content row py-2 mx-0">
        @foreach($products as $product)
            <div class="col-12 py-2">
                <div class="row">
                    <div class="col-3 product__thumbnail">
                        <a href="{{ $product->url }}" class="img-fluid-eq">
                            <div class="img-fluid-eq__dummy dummy-mt-8"></div>
                            <div class="img-fluid-eq__wrap">
                                <img class="product-thumbnail__img"
                                    src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                    alt="{{ $product->name }}">
                            </div>
                        </a>
                    </div>
                    <div class="col-9 product__content">
                        <a class="product__title" href="{{ $product->url }}">{{ $product->name }}</a>
                        @if (EcommerceHelper::isReviewEnabled() && $product->reviews_avg > 0)
                            {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                        @endif
                        {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                    </div>
                </div>
            </div>
        @endforeach
        @if ($products->hasMorePages() && $products->nextPageUrl())
            <div class="col-12 text-center loadmore-container">
                <button class="btn loadmore position-relative mx-auto" href="{{ $products->withQueryString()->nextPageUrl() }}">
                    <span>{{ __('Load more') }}</span>
                    <span class="svg-icon ms-1">
                        <svg>
                            <use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use>
                        </svg>
                    </span>
                </button>
            </div>
        @endif
    </div>
    <div class="panel__footer text-center">
        <a href="{{ route('public.products', $queries) }}">{{ __('See all results') }}</a>
    </div>
@else
    <div class="panel__content row py-2 mx-0">
        <div class="text-center">{{ __('No products found.') }}</div>
    </div>
@endif
