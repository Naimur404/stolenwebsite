<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3 @if ($errors->has('name')) has-error @endif">
            <label class="text-title-field">{{ trans('plugins/ecommerce::addresses.name') }}</label>
            <input type="text" name="name" id="address_name" placeholder="{{ trans('plugins/ecommerce::addresses.name_placeholder') }}" class="form-control"
                   value="{{ old('name', $address) }}">
            {!! Form::error('name', $errors) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3 @if ($errors->has('phone')) has-error @endif">
            <label class="text-title-field">{{ trans('plugins/ecommerce::addresses.phone') }}</label>
            <input type="text" name="phone" id="address_phone" placeholder="{{ trans('plugins/ecommerce::addresses.phone_placeholder') }}" class="form-control"
                   value="{{ old('phone', $address) }}">
            {!! Form::error('phone', $errors) !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3 @if ($errors->has('zip_code')) has-error @endif">
            <label class="text-title-field">{{ trans('plugins/ecommerce::addresses.zip') }}</label>
            <input type="text" name="zip_code" id="address_zip_code" placeholder="{{ trans('plugins/ecommerce::addresses.zip_placeholder') }}" class="form-control"
                   value="{{ old('zip_code', $address) }}">
            {!! Form::error('zip_code', $errors) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3 @if ($errors->has('email')) has-error @endif">
            <label class="text-title-field">{{ trans('plugins/ecommerce::addresses.email') }}</label>
            <input type="text" name="email" id="address_email" placeholder="{{ trans('plugins/ecommerce::addresses.email_placeholder') }}" class="form-control"
                   value="{{ old('email', $address) }}">
            {!! Form::error('email', $errors) !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="form-group mb-3 @if ($errors->has('address')) has-error @endif">
            <label class="text-title-field">{{ trans('plugins/ecommerce::addresses.address') }}</label>
            <input type="text" name="address" id="address_address" placeholder="{{ trans('plugins/ecommerce::addresses.address_placeholder') }}" class="form-control"
                   value="{{ old('address', $address) }}">
            {!! Form::error('address', $errors) !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        @if (EcommerceHelper::isUsingInMultipleCountries())
            <div class="form-group mb-3 @if ($errors->has('country')) has-error @endif">
                <label for="country">{{ __('Country') }}:</label>
                {!! Form::customSelect('country', EcommerceHelper::getAvailableCountries(), old('country', $address->country), ['id' => 'country', 'data-type' => 'country']) !!}
            </div>
            {!! Form::error('country', $errors) !!}
        @else
            <input type="hidden" name="country" value="{{ EcommerceHelper::getFirstCountryId() }}">
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3 @if ($errors->has('state')) has-error @endif">
            <label class="text-title-field">{{ trans('plugins/ecommerce::addresses.state') }}</label>
            @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
                {!! Form::customSelect('country', EcommerceHelper::getAvailableStatesByCountry(old('country', $address->country)), old('state', $address->state), ['id' => 'state', 'data-type' => 'state', 'data-url' => route('ajax.states-by-country')]) !!}
            @else
                <input id="state" type="text" class="form-control" name="state" value="{{ old('state', $address) }}">
            @endif
            {!! Form::error('state', $errors) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3 @if ($errors->has('city')) has-error @endif">
            <label class="text-title-field">{{ trans('plugins/ecommerce::addresses.city') }}</label>
            @if (EcommerceHelper::useCityFieldAsTextField())
                <input id="city" type="text" class="form-control" name="city" value="{{ old('city', $address) }}">
            @else
                {!! Form::customSelect('city', EcommerceHelper::getAvailableStatesByCountry(old('state', $address->state)), old('city', $address->city), ['id' => 'city', 'data-type' => 'city', 'data-url' => route('ajax.cities-by-state')]) !!}
            @endif
            {!! Form::error('city', $errors) !!}
        </div>
    </div>
</div>
