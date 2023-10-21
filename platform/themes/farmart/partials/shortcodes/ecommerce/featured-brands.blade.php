@php
    $slick = [
        'rtl'            => BaseHelper::siteLanguageDirection() == 'rtl',
        'appendArrows'   => '.arrows-wrapper',
        'arrows'         => true,
        'dots'           => false,
        'autoplay'       => $shortcode->is_autoplay == 'yes',
        'infinite'        => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
        'autoplaySpeed'  => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 3000,
        'speed'          => 800,
        'slidesToShow'   => 4,
        'slidesToScroll' => 1,
        'responsive'     => [
            [
                'breakpoint' => 1024,
                'settings'   => [
                    'slidesToShow' => 2,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings'   => [
                    'arrows'         => false,
                    'dots'           => true,
                    'slidesToShow'   => 2,
                    'slidesToScroll' => 1,
                ],
            ],
        ],
    ];
    $brands = get_featured_brands();
@endphp
<div class="widget-featured-brands py-5">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{{ $shortcode->title }}</h2>
                </div>
                <div class="featured-brands__body arrows-top-right">
                    <div class="featured-brands-body slick-slides-carousel" data-slick="{{ json_encode($slick) }}">
                        @foreach ($brands as $brand)
                            <div class="featured-brand-item">
                                <div class="brand-item-body mx-2 py-4 px-2">
                                    <a class="py-3" href="{{ $brand->url }}">
                                        <div class="brand__thumb mb-3 img-fluid-eq">
                                            <div class="img-fluid-eq__dummy"></div>
                                            <div class="img-fluid-eq__wrap">
                                                <img
                                                    class="lazyload mx-auto"
                                                    src="{{ image_placeholder($brand->logo) }}"
                                                    data-src="{{ RvMedia::getImageUrl($brand->logo, null, false, RvMedia::getDefaultImage()) }}"
                                                    alt="{{ $brand->name }}"
                                                />
                                            </div>
                                        </div>
                                        <div class="brand__text py-3">
                                            <span class="h6 fw-bold text-secondary text-uppercase brand__name">
                                                {{ $brand->name }}
                                            </span>
                                            <div class="h5 fw-bold brand__desc">
                                                <div>
                                                    {!! BaseHelper::clean(Str::limit($brand->description, 150)) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="arrows-wrapper"></div>
                </div>
            </div>
        </div>
    </div>
</div>
