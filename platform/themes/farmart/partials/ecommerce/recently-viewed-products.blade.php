<div class="recently-product-wrapper">
    @if ($products->isNotEmpty())
        <ul class="product-list"
            data-slick="{{ json_encode(['arrows' => true, 'dots' => false, 'autoplay' => false, 'infinite' => true, 'slidesToShow' => 10]) }}">
            @foreach ($products as $product)
                <li class="product">
                    <a href="{{ $product->url }}">
                        <img src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}"/>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="recently-empty-products text-center">
            <div class="empty-desc">
                <span>{{ __('Recently Viewed Products is a function which helps you keep track of your recent viewing history.') }}</span>
                <a class="text-primary" href="{{ route('public.products') }}">{{ __('Shop Now') }}</a>
            </div>
        </div>
    @endif
<div>
