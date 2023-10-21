@php Theme::set('withTitle', false); @endphp

<div class="row mt-5">
    <div class="col-md-9">
        <h1 class="h2">{{ $post->name }}</h1>
        <div class="post-item__inner pb-4 my-3 border-bottom">
            <div class="entry-meta">
                @if ($post->author)
                    <div class="entry-meta-author">
                        <span>{{ __('By :name', ['name' => $post->author->name]) }}</span>
                    </div>
                @endif
                @if ($post->categories->count())
                    <div class="entry-meta-categories">
                        <span>{{ __('in') }}</span>
                        @foreach($post->categories as $category)
                            <a href="{{ $category->url }}">{{ $category->name }}</a> @if (!$loop->last) , @endif
                        @endforeach
                    </div>
                @endif
                <div class="entry-meta-date">
                    <span>{{ __('on') }}</span>
                    <time>{{ $post->created_at->translatedFormat('M d, Y') }}</time>
                </div>
            </div>
        </div>
        <div class="mt-5 pt-3 post-detail__content">
            <div class="ck-content">{!! BaseHelper::clean($post->content) !!}</div>

            @if ($post->tags->count())
                <div class="entry-meta-tags">
                    <strong>{{ __('Tags') }}:</strong>
                    @foreach($post->tags as $tag)
                        <a href="{{ $tag->url }}" class="text-link">{{ $tag->name }}</a>@if (!$loop->last), @endif
                    @endforeach
                </div>
            @endif

            @if (theme_option('facebook_comment_enabled_in_post', 'yes') == 'yes')
                <br />
                {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, Theme::partial('comments')) !!}
            @endif
        </div>
        @php $relatedPosts = get_related_posts($post->id, 4); @endphp

        @if ($relatedPosts->count())
            <div class="related-posts mt-5 pt-3">
                <div class="heading">
                    <h3>{{ __('Related Posts') }}</h3>
                </div>
                <div class="list-post--wrapper">
                    <div class="slick-slides-carousel" data-slick="{{ json_encode([
                        'slidesToShow'   => 3,
                        'slidesToScroll' => 1,
                        'arrows'         => true,
                        'dots'           => true,
                        'infinite'        => false,
                        'responsive'     => [
                            [
                                'breakpoint' => 1200,
                                'settings'   => [
                                    'slidesToShow'   => 2,
                                    'slidesToScroll' => 1
                                ],
                            ],
                            [
                                'breakpoint' => 480,
                                'settings'   => [
                                    'slidesToShow'   => 1,
                                    'slidesToScroll' => 1
                                ],
                            ],
                        ],
                    ]) }}">
                        @foreach ($relatedPosts as $item)
                            {!! Theme::partial('post-item', ['post' => $item]) !!}
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="col-md-3">
        <div class="primary-sidebar">
            <aside class="widget-area" id="primary-sidebar">
                {!! dynamic_sidebar('primary_sidebar') !!}
            </aside>
        </div>
    </div>
</div>
