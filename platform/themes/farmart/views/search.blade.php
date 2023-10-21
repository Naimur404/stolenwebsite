@php
    Theme::layout('full-width')
@endphp

@if ($posts->count() > 0)
    @include(Theme::getThemeNamespace() . '::views.loop', compact('posts'))
@endif
