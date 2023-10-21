<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="{{ __('Title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Subtitle') }}</label>
    <input type="text" name="subtitle" value="{{ Arr::get($attributes, 'subtitle') }}" class="form-control" placeholder="{{ __('Subtitle') }}">
</div>

<div class="p-2 border">
    @for ($i = 1; $i <= 3; $i++)
        <div class="p-2 border mb-2">
            <div class="form-group">
                <label class="control-label">{{ __('Name :number', ['number' => $i]) }}</label>
                <input type="text" name="name_{{ $i }}" value="{{ Arr::get($attributes, 'name_' . $i) }}" class="form-control" placeholder="{{ __('Name :number', ['number' => $i]) }}">
            </div>
            <div class="form-group">
                <label class="control-label">{{ __('Address :number', ['number' => $i]) }}</label>
                <input type="text" name="address_{{ $i }}" value="{{ Arr::get($attributes, 'address_' . $i) }}" class="form-control" placeholder="{{ __('Address :number', ['number' => $i]) }}">
            </div>
            <div class="form-group">
                <label class="control-label">{{ __('Phone :number', ['number' => $i]) }}</label>
                <input type="text" name="phone_{{ $i }}" value="{{ Arr::get($attributes, 'phone_' . $i) }}" class="form-control" placeholder="{{ __('Phone :number', ['number' => $i]) }}">
            </div>
            <div class="form-group">
                <label class="control-label">{{ __('Email :number', ['number' => $i]) }}</label>
                <input type="text" name="email_{{ $i }}" value="{{ Arr::get($attributes, 'email_' . $i) }}" class="form-control" placeholder="{{ __('Email :number', ['number' => $i]) }}">
            </div>
        </div>
    @endfor
    <div class="help-block">
        <small>{{ __('You can add up to 3 contact info boxes, to show is required Name and Address') }}</small>
    </div>
</div>

@if (is_plugin_active('contact'))
    <div class="form-group">
        <label class="control-label">{{ __('Show Contact form') }}</label>
        <div class="ui-select-wrapper form-group">
            <select name="show_contact_form" class="ui-select">
                <option value="0" @if (0 == Arr::get($attributes, 'show_contact_form')) selected @endif>{{ __('No') }}</option>
                <option value="1" @if (1 == Arr::get($attributes, 'show_contact_form')) selected @endif>{{ __('Yes') }}</option>
            </select>
            <svg class="svg-next-icon svg-next-icon-size-16">
                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
            </svg>
        </div>
    </div>
@endif
