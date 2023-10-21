<div class="form-group">
    <label class="control-label">{{ __('Select category') }}</label>
    {!! Form::customSelect('category_id', $categories, Arr::get($attributes, 'category_id')) !!}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit number of categories') }}</label>
    <input type="number" name="number_of_categories" value="{{ Arr::get($attributes, 'number_of_categories', 3) }}" class="form-control" placeholder="{{ __('Default: 3') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit number of products') }}</label>
    <input type="number" name="limit" value="{{ Arr::get($attributes, 'limit') }}" class="form-control" placeholder="{{ __('Unlimited by default') }}">
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
