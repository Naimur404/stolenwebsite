<div class="customer-billing-address-form">
    @php
        $oldSessionAddressId = null;
        $billingAddressSameAsShippingAddress = old('billing_address_same_as_shipping_address', Arr::get($sessionCheckoutData, 'billing_address_same_as_shipping_address', true));
    @endphp
    <div class="mb-3 form-group">
        <input type="hidden" name="billing_address_same_as_shipping_address" value="0">
        @if ($isShowAddressForm)
            <input type="checkbox" name="billing_address_same_as_shipping_address" value="1" id="billing_address_same_as_shipping_address" @checked ($billingAddressSameAsShippingAddress)>
            <label for="billing_address_same_as_shipping_address" class="control-label">{{ __('Same as shipping information') }}</label>
        @elseif (auth('customer')->check() && $isAvailableAddress)
            <input type="hidden" name="billing_address_same_as_shipping_address" value="1">
            @php
                $oldSessionAddressId = old('address.address_id', $sessionAddressId)
            @endphp
            <div class="select--arrow form-input-wrapper">
                <select name="address[address_id]" class="form-control" id="billing_address_id">
                    <option value="">{{ __('Select billing address...') }}</option>
                    @foreach ($addresses as $address)
                        <option value="{{ $address->id }}" @selected($oldSessionAddressId == $address->id)>{{ $address->full_address }}</option>
                    @endforeach
                </select>
                <i class="fas fa-angle-down"></i>
            </div>
            <br>
        @endif
    </div>

    <div class="billing-address-form-wrapper" @if (($oldSessionAddressId && $oldSessionAddressId != 'new') || ($isShowAddressForm && $billingAddressSameAsShippingAddress)) style="display: none" @endif>
        <div class="form-group mb-3 @error('billing_address.name') has-error @enderror">
            <div class="form-input-wrapper">
                <input type="text" name="billing_address[name]" id="billing-address-name" class="form-control"
                    value="{{ old('billing_address.name', Arr::get($sessionCheckoutData, 'billing_address.name')) ?: (auth('customer')->check() ? auth('customer')->user()->name : null) }}">
                <label for='billing-address-name'>{{ __('Full Name') }}</label>
            </div>
            {!! Form::error('billing_address.name', $errors) !!}
        </div>

        <div class="row">
            <div class="col-lg-8 col-12">
                <div class="form-group  @error('billing_address.email') has-error @enderror">
                    <div class="form-input-wrapper">
                        <input type="email" name="billing_address[email]" id="billing-address-email" class="form-control"
                            value="{{ old('billing_address.email', Arr::get($sessionCheckoutData, 'billing_address.email')) ?: (auth('customer')->check() ? auth('customer')->user()->email : null) }}">
                        <label for='billing-address-email'>{{ __('Email') }}</label>
                    </div>
                    {!! Form::error('billing_address.email', $errors) !!}
                </div>
            </div>
            <div class="col-lg-4 col-12">
                <div class="form-group  @error('billing_address.phone') has-error @enderror">
                    <div class="form-input-wrapper">
                        {!! Form::text('billing_address[phone]', old('billing_address.phone', Arr::get($sessionCheckoutData, 'billing_address.phone')) ?: (auth('customer')->check() ? auth('customer')->user()->phone : null), ['id' => 'billing-address-phone', 'class' => 'form-control']) !!}
                        <label>{{ __('Phone') }}</label>
                    </div>
                    {!! Form::error('billing_address.phone', $errors) !!}
                </div>
            </div>
        </div>

        <div class="form-group mb-3 @error('billing_address.country') has-error @enderror">
            @if (EcommerceHelper::isUsingInMultipleCountries())
                <div class="select--arrow form-input-wrapper">
                    <select name="billing_address[country]" class="form-control"
                        data-form-parent=".customer-billing-address-form" id="billing-address-country" data-type="country">
                        @foreach(EcommerceHelper::getAvailableCountries() as $countryCode => $countryName)
                            <option value="{{ $countryCode }}" @if (old('billing_address.country', Arr::get($sessionCheckoutData, 'billing_address.country')) == $countryCode) selected @endif>{{ $countryName }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-angle-down"></i>
                    <label for='billing-address-country'>{{ __('Country') }}</label>
                </div>
            @else
                <input type="hidden" name="billing_address[country]" id="billing-address-country" value="{{ EcommerceHelper::getFirstCountryId() }}">
            @endif
            {!! Form::error('billing_address.country', $errors) !!}
        </div>

        <div class="row">
            <div class="col-sm-6 col-12">
                <div class="form-group mb-3 @error('billing_address.state') has-error @enderror">
                    @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
                        <div class="select--arrow form-input-wrapper">
                            <select name="billing_address[state]" class="form-control"
                                    data-form-parent=".customer-billing-address-form" id="billing-address-state" data-type="state" data-url="{{ route('ajax.states-by-country') }}">
                                <option value="">{{ __('Select state...') }}</option>
                                @if (old('billing_address.country', Arr::get($sessionCheckoutData, 'billing_address.country')) || !EcommerceHelper::isUsingInMultipleCountries())
                                    @foreach(EcommerceHelper::getAvailableStatesByCountry(old('billing_address.country', Arr::get($sessionCheckoutData, 'billing_address.country'))) as $stateId => $stateName)
                                        <option value="{{ $stateId }}" @if (old('billing_address.state', Arr::get($sessionCheckoutData, 'billing_address.state')) == $stateId) selected @endif>{{ $stateName }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <i class="fas fa-angle-down"></i>
                            <label for='billing-address-state'>{{ __('State') }}</label>
                        </div>
                    @else
                        <div class="form-input-wrapper">
                            <input id="billing-address-state" type="text" class="form-control" name="billing_address[state]" value="{{ old('billing_address.state', Arr::get($sessionCheckoutData, 'billing_address.state')) }}">
                            <label for='billing-address-state'>{{ __('State') }}</label>
                        </div>
                    @endif
                    {!! Form::error('billing_address.state', $errors) !!}
                </div>
            </div>

            <div class="col-sm-6 col-12">
                <div class="form-group  @error('billing_address.city') has-error @enderror">
                    @if (EcommerceHelper::useCityFieldAsTextField())
                        <div class="form-input-wrapper">
                            <input id="billing-address-city" type="text" class="form-control" name="billing_address[city]" value="{{ old('billing_address.city', Arr::get($sessionCheckoutData, 'billing_address.city')) }}">
                            <label for='billing-address-city'>{{ __('City') }}</label>
                        </div>
                    @else
                        <div class="select--arrow form-input-wrapper">
                            <select name="billing_address[city]" class="form-control" id="billing-address-city" data-type="city" data-using-select2="false" data-url="{{ route('ajax.cities-by-state') }}">
                                <option value="">{{ __('Select city...') }}</option>
                                @if (old('billing_address.state', Arr::get($sessionCheckoutData, 'billing_address.state')))
                                    @foreach(EcommerceHelper::getAvailableCitiesByState(old('billing_address.state', Arr::get($sessionCheckoutData, 'billing_address.state'))) as $cityId => $cityName)
                                        <option value="{{ $cityId }}" @if (old('billing_address.city', Arr::get($sessionCheckoutData, 'billing_address.city')) == $cityId) selected @endif>{{ $cityName }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <i class="fas fa-angle-down"></i>
                            <label for='billing-address-city'>{{ __('City') }}</label>
                        </div>
                    @endif
                    {!! Form::error('billing_address.city', $errors) !!}
                </div>
            </div>
        </div>

        <div class="form-group mb-3 @error('billing_address.address') has-error @enderror">
            <div class="form-input-wrapper">
                <input id="billing-address-address" type="text" class="form-control"
                    name="billing_address[address]" value="{{ old('billing_address.address', Arr::get($sessionCheckoutData, 'billing_address.address')) }}">
                <label for='billing-address-address'>{{ __('Address') }}</label>
            </div>
            {!! Form::error('billing_address.address', $errors) !!}
        </div>

        @if (EcommerceHelper::isZipCodeEnabled())
            <div class="form-group mb-3 @error('billing_address.zip_code') has-error @enderror">
                <div class="form-input-wrapper">
                    <input id="billing-address-zip-code" type="text" class="form-control" name="billing_address[zip_code]" value="{{ old('billing_address.zip_code', Arr::get($sessionCheckoutData, 'billing_address.zip_code')) }}">
                    <label for='billing-address-zip-code'>{{ __('Zip code') }}</label>
                </div>
                {!! Form::error('billing_address.zip_code', $errors) !!}
            </div>
        @endif
    </div>
</div>
