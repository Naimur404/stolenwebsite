@php
    $slickConfig = [
        'arrows'         => false,
        'dots'           => false,
        'autoplay'       => false,
        'infinite'        => false,
        'autoplaySpeed'  => 3000,
        'speed'          => 800,
        'slidesToShow'   => 2,
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
                    'arrows'       => false,
                    'dots'         => true,
                    'slidesToShow' => 1,
                ],
            ],
        ],
    ];

    if (!$shortcode->app_enabled) {
        $slickConfig['slidesToShow'] = 3;
    }

    $posts = get_featured_posts(!$shortcode->app_enabled ? 3 : 2, ['author', 'categories:id,name', 'categories.slugable']);
@endphp

<div class="widget-blog py-5 lazyload" @if ($shortcode->bg) data-bg="{{ RvMedia::getImageUrl($shortcode->bg) }}" @endif>
    <div class="container-xxxl">
        <div class="row">
            <div class="@if ($shortcode->app_enabled) col-lg-8 @else col-12 @endif py-4 py-lg-0">
                <div class="row justify-content-between align-items-center widget-header ms-0 me-0">
                    <h2 class="col-auto mb-0 py-2 ps-0">{{ $shortcode->title }}</h2>
                    <a class="col-auto pe-0" href="{{ get_blog_page_url() }}">
                        <span class="link-text">{{ __('All Articles') }}
                            <span class="svg-icon">
                                <svg>
                                    <use href="#svg-icon-chevron-right" xlink:href="#svg-icon-chevron-right"></use>
                                </svg>
                            </span>
                        </span>
                    </a>
                </div>
                <div class="col slick-slides-carousel widget-blog-container position-relative"
                    data-slick="{{ json_encode($slickConfig) }}">
                    @foreach ($posts as $post)
                        <article class="post-item-wrapper">
                            <div class="card post-item__inner">
                                <div class="row g-0">
                                    <div class="col-md-4 post-item__image">
                                        <a class="img-fluid-eq" href="{{ $post->url }}">
                                            <div class="img-fluid-eq__dummy"></div>
                                            <div class="img-fluid-eq__wrap">
                                                <img class="lazyload" alt="{{ $post->name }}" src="{{ image_placeholder($post->image) }}" data-src="{{ RvMedia::getImageUrl($post->image, null, false, RvMedia::getDefaultImage()) }}">
                                            </div>
                                        </a></div>
                                    <div class="col-md-8 post-item__content">
                                        <div>
                                            <div class="entry-meta">
                                                @if ($post->author)
                                                    <div class="entry-meta-author">
                                                        <span>{{ __('By') }}</span>
                                                        <strong>{{ $post->author->name }}</strong>
                                                    </div>
                                                @endif
                                                @if ($post->categories->count())
                                                    <div class="entry-meta-categories">
                                                        <span>{{ __('in') }}</span>
                                                        <a href="{{ $post->firstCategory->url }}">{{ $post->firstCategory->name }}</a>
                                                    </div>
                                                @endif
                                                <div class="entry-meta-date">
                                                    <span>{{ __('on') }}</span>
                                                    <time>{{ $post->created_at->translatedFormat('M d, Y') }}</time>
                                                </div>
                                            </div>
                                            <div class="entry-title mb-3 mt-2">
                                                <p class="h4 text-truncate"><a href="{{ $post->url }}">{{ $post->name }}</a></p>
                                            </div>
                                            <div class="entry-description">
                                                <p>{{ Str::words($post->description, 20) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
            @if ($shortcode->app_enabled)
                <div class="col-lg-4 py-4 py-lg-0">
                    <div class="widget-wrapper widget-mobile-apps h-100 lazyload"
                        @if ($shortcode->app_bg) data-bg="{{ RvMedia::getImageUrl($shortcode->app_bg) }}" @endif>
                        <div class="widget-header text-center me-0">
                            <h2>{!! BaseHelper::clean($shortcode->app_title) !!}</h2>
                        </div>
                        <div class="widget-subtitle text-center">
                            <p class="my-3">{!! BaseHelper::clean($shortcode->app_description) !!}</p>
                            <div>
                                @if ($shortcode->app_ios_img && $shortcode->app_ios_link)
                                    <a href="{{ url($shortcode->app_ios_link) }}" title="iOS">
                                        <img class="my-4 mx-2 lazyload" data-src="{{ RvMedia::getImageUrl($shortcode->app_ios_img) }}" alt="iOS">
                                    </a>
                                @endif
                                @if ($shortcode->app_android_img && $shortcode->app_android_link)
                                    <a href="{{ url($shortcode->app_android_link) }}" title="Android">
                                        <img class="my-4 mx-2 lazyload" data-src="{{ RvMedia::getImageUrl($shortcode->app_android_img) }}" alt="Android">
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
