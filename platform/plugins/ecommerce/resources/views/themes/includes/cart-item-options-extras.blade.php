@if (($extras = Arr::get($options, 'extras', [])) && is_array($extras))
    @foreach($extras as $extra)
        @if (! empty($extra['key']) && !empty($extra['value']))
            <p class="mb-0">
                <small>{{ $extra['key'] }}: <strong> {{ $extra['value'] }}</strong></small>
            </p>
        @endif
    @endforeach
@endif
