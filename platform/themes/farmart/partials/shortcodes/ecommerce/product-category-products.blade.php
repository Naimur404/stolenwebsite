<div class="widget-products-with-category py-5 bg-light">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{{ $shortcode->title ?: $category->name }}</h2>
                </div>
                <div class="product-deals-day__body arrows-top-right">
                    <div
                        class="product-deals-day-body slick-slides-carousel"
                        data-slick="{{ json_encode([
                        'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                        'appendArrows' => '.arrows-wrapper',
                        'arrows' => true,
                        'dots' => false,
                        'autoplay' => $shortcode->is_autoplay == 'yes',
                        'infinite' => ($shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes'),
                        'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 3000,
                        'speed' => 800,
                        'slidesToShow' => 6,
                        'slidesToScroll' => 1,
                        'swipeToSlide' => true,
                        'responsive' => [
                            [
                                'breakpoint' => 1400,
                                'settings' => [
                                    'slidesToShow' => 5,
                                ],
                            ],
                            [
                                'breakpoint' => 1199,
                                'settings' => [
                                    'slidesToShow' => 4,
                                ],
                            ],
                            [
                                'breakpoint' => 1024,
                                'settings' => [
                                    'slidesToShow' => 3,
                                ],
                            ],
                            [
                                'breakpoint' => 767,
                                'settings' => [
                                    'arrows' => true,
                                    'dots' => false,
                                    'slidesToShow' => 2,
                                    'slidesToScroll' => 2,
                                ],
                            ],
                        ],
                    ]) }}"
                    >
                        @foreach($products as $product)
                            <div class="product-inner">
                                {!! Theme::partial('ecommerce.product-item', compact('product', 'wishlistIds')) !!}
                            </div>
                        @endforeach
                    </div>
                    <div class="arrows-wrapper"></div>
            </div>
        </div>
    </div>
</div>
</div>
