@if (is_plugin_active('blog'))
    <div class="widget-sidebar widget-search my-4">
        <form class="search-form" role="search" method="GET" action="{{ route('public.search') }}">
            <label><span class="screen-reader-text">{{ $config['name'] ?: __('Search for') }}:</span>
                <input class="search-field" type="search" placeholder="{{ __('Search...') }}" value="{{ BaseHelper::stringify(request()->query('q')) }}" name="q">
            </label>
            <input class="search-submit" type="submit" value="Search">
        </form>
    </div>
@endif
