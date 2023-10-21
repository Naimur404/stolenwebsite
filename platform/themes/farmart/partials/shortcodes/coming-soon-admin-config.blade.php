<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="{{ __('Title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Subtitle') }}</label>
    <input type="text" name="subtitle" value="{{ Arr::get($attributes, 'subtitle') }}" class="form-control" placeholder="{{ __('Subtitle') }}">
</div>

<div class="form-group">
    <label class="control-label">Time</label>
    <input type="text" name="time" value="{{ Arr::get($attributes, 'time') }}" class="form-control" placeholder="Time">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Connect social networks title') }}</label>
    <input type="text" name="social_title" value="{{ Arr::get($attributes, 'social_title') }}" class="form-control" placeholder="{{ __('Connect social networks title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Image') }}</label>
    {!! Form::mediaImage('image', Arr::get($attributes, 'image')) !!}
</div>
