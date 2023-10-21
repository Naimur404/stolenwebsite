<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="{{ __('Title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit') }}</label>
    <input type="number" name="limit" value="{{ Arr::get($attributes, 'limit', 8) }}" class="form-control" placeholder="{{ __('Limit') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Select a product collection') }}</label>
    <div class="ui-select-wrapper form-group">
        <select name="collection_id" class="ui-select">
            <option value="">{{ __('All') }}</option>
            @foreach ($productCollections as $collection)
                <option value="{{ $collection->id }}" @if ($collection->id == Arr::get($attributes, 'collection_id')) selected @endif>{!! BaseHelper::clean($collection->indent_text) !!} {{ $collection->name }}</option>
            @endforeach
        </select>
        <svg class="svg-next-icon svg-next-icon-size-16">
            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
        </svg>
    </div>
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
