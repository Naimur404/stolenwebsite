<div class="form-group">
    <label class="control-label">{{ __('Background Image') }}</label>
    {!! Form::mediaImage('background', Arr::get($attributes, 'background')) !!}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Ads') }}</label>
    <div class="ui-select-wrapper form-group">
        <select name="ads" class="form-control ui-select">
            <option value="">{{ __('-- select --') }}</option>
            @foreach($ads as $ad)
                <option value="{{ $ad->key }}" @if ($ad->key == Arr::get($attributes, 'ads')) selected @endif>{{ $ad->name }}</option>
            @endforeach
        </select>
        <svg class="svg-next-icon svg-next-icon-size-16">
            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
        </svg>
    </div>
</div>

