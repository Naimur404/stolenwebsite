@php
    SeoHelper::setTitle(__('404 - Not found'));
    Theme::fireEventGlobalAssets();
    AdminBar::setIsDisplay(false);
@endphp

{!! Theme::partial('header') !!}

<div id="main-content">
    <div class="container-xxxl">
        <div class="row justify-content-center">
            <div class="col-md-6 mt-5">
                <div class="error-404 not-found text-center my-5">
                    @if (theme_option('404_page_image'))
                        <img src="{{ RvMedia::getImageUrl(theme_option('404_page_image')) }}" alt="404">
                    @else
                        <img src="{{ Theme::asset()->url('images/404.png') }}" alt="404">
                    @endif
                    <h2 class="fw-bold h1 page-title">{{ __('Oops! Page not found.') }}</h2>
                    <div class="page-content">
                        <div class="my-3">{{ __("We can't find the page you're looking for.") }} {{ __('You can either')}}
                            <a class="text-primary" href="javascript:history.go(-1)">{{ __('return to the previous page') }}</a>,
                            <a class="text-primary" href="{{ route('public.index') }}">{{ __('visit our home page') }}</a>
                            @if (is_plugin_active('blog') || is_plugin_active('ecommerce'))
                                {{ __('or search for something else.') }}
                            @endif
                        </div>
                        @if (is_plugin_active('ecommerce') || is_plugin_active('blog'))
                            <form class="search-form" role="search" method="GET" action="{{ is_plugin_active('ecommerce') ? route('public.products') : route('public.search') }}">
                                <label>
                                    <span class="screen-reader-text">{{ __('Search for') }}:</span>
                                    <input class="search-field" type="search" placeholder="{{ __('Search...') }}" name="q">
                                </label>
                                <input class="search-submit" type="submit" value="{{ __('Search') }}">
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{!! Theme::partial('footer') !!}


