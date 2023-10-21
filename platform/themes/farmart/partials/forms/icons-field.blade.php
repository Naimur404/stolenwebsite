@php
    Arr::set($attributes, 'class', Arr::get($attributes, 'class') . ' icon-select');
    Arr::set($attributes, 'data-empty-value', __('-- None --'));
@endphp

{!! Form::customSelect($name, [$value => $value], $value, $attributes) !!}

@once
    @if (request()->ajax())
        <link media="all" type="text/css" rel="stylesheet" href="{{ Theme::asset()->url('fonts/Linearicons/Linearicons/Font/demo-files/demo.css') }}">
        <script src="{{ Theme::asset()->url('js/icons-field.js') }}?v=1.0.0"></script>
    @else
        @push('header')
            <link media="all" type="text/css" rel="stylesheet" href="{{ Theme::asset()->url('fonts/Linearicons/Linearicons/Font/demo-files/demo.css') }}">
            <script src="{{ Theme::asset()->url('js/icons-field.js') }}?v=1.0.0"></script>
        @endpush
    @endif
@endonce
