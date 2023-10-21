@if (is_plugin_active('blog'))
    @php
        $posts = get_recent_posts($config['number_display']);
    @endphp
    @if ($posts->count())
        <div class="widget-sidebar widget-blog-latest-post">
            <h2 class="widget-title">{{ $config['name'] ?: __('Recent Post') }}</h2>
            <div class="widget__inner">
                @foreach ($posts as $post)
                    <div class="card border-0 post-item-small mb-3">
                        <div class="row g-2">
                            <div class="col-3">
                                <a class="img-fluid-eq" href="{{ $post->url }}" title="{{ $post->name }}">
                                    <div class="img-fluid-eq__dummy"></div>
                                    <div class="img-fluid-eq__wrap">
                                        <img class="post-item-image lazyload"
                                            data-src="{{ RvMedia::getImageUrl($post->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $post->name }}">
                                    </div>
                                </a>
                            </div>
                            <div class="col-9">
                                <div class="entry-meta">
                                    <div class="entry-meta-date">
                                        <time class="entry-date" datetime="{{ $post->created_at }}">{{ $post->created_at->translatedFormat('M d, Y') }}</time>
                                        @if ($post->author)
                                            <span class="d-inline-block">{{ __('by') }}</span> <span class="d-inline-block author-name">{{ $post->author->name }}</span>
                                        @endif
                                    </div>
                                </div>
                                <h6 class="entry-title">
                                    <a href="{{ $post->url }}">{{ $post->name }}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
