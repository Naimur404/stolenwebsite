<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="{{ __('Title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Select a flash sale') }}</label>
    <select name="flash_sale_id" class="form-control">
        @foreach($flashSales as $flashSale)
            <option value="{{ $flashSale->id }}" @if ($flashSale->id == Arr::get($attributes, 'flash_sale_id')) selected @endif>{{ $flashSale->name }}</option>
        @endforeach
    </select>
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
