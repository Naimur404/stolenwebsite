<div class="form-group">
    <label>{{ trans('core/base::forms.name') }}</label>
    <input type="text" class="form-control" name="name" value="{{ $config['name'] }}">
</div>

<div class="form-group">
    <label>{{ trans('core/base::forms.description') }}</label>
    <textarea class="form-control" rows="3" name="about">{{ $config['about'] }}</textarea>
</div>

<div class="form-group">
    <label>{{ __('Address') }}</label>
    <input type="text" class="form-control" name="address" value="{{ $config['address'] }}">
</div>

<div class="form-group">
    <label>{{ __('Phone') }}</label>
    <input type="text" class="form-control" name="phone" value="{{ $config['phone'] }}">
</div>

<div class="form-group">
    <label>{{ __('Email') }}</label>
    <input type="email" class="form-control" name="email" value="{{ $config['email'] }}">
</div>

<div class="form-group">
    <label>{{ __('Working time') }}</label>
    <input type="text" class="form-control" name="working_time" value="{{ $config['working_time'] }}">
</div>
