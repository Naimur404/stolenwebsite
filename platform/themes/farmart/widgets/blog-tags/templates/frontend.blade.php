@if (is_plugin_active('blog'))
    @php
        $tags = get_popular_tags($config['number_display']);
    @endphp
    @if ($tags->count())
        <div class="widget-sidebar widget-blog-tag-cloud">
            <h2 class="widget-title">{{ BaseHelper::clean($config['name'] ?: __('Tags')) }}</h2>
            <div class="widget__inner">
                @foreach ($tags as $tag)
                    <a class="tag-cloud-link" href="{{ $tag->url }}" aria-label="{{ $tag->name }}" title="{{ $tag->name }}">{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>
    @endif
@endif
