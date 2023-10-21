{!! Form::open(['url' => $locator ? route('ecommerce.store-locators.edit.post', $locator->id) : route('ecommerce.store-locators.create')]) !!}
<div class="next-form-section">
    <div class="next-form-grid">
        <div class="next-form-grid-cell">
            <label class="text-title-field">{{ trans('plugins/ecommerce::store-locator.store_name') }}</label>
            <input type="text" class="next-input" name="name" placeholder="{{ trans('plugins/ecommerce::store-locator.store_name') }}" value="{{ $locator ? $locator->name : null }}">
        </div>
        <div class="next-form-grid-cell">
            <label class="text-title-field">{{ trans('plugins/ecommerce::store-locator.phone') }}</label>
            <input type="text" class="next-input" name="phone" placeholder="{{ trans('plugins/ecommerce::store-locator.phone') }}" value="{{ $locator ? $locator->phone : null }}">
        </div>
    </div>
    <div class="next-form-grid">
        <div class="next-form-grid-cell">
            <label class="text-title-field">{{ trans('plugins/ecommerce::store-locator.email') }}</label>
            <input type="text" class="next-input" name="email" placeholder="{{ trans('plugins/ecommerce::store-locator.email') }}" value="{{ $locator ? $locator->email : null }}">
        </div>
    </div>
    <div class="next-form-grid">
        <div class="next-form-grid-cell">
            <label class="text-title-field">{{ trans('plugins/ecommerce::store-locator.address') }}</label>
            <input type="text" class="next-input" name="address" placeholder="{{ trans('plugins/ecommerce::store-locator.address') }}" value="{{ $locator ? $locator->address : null}}">
        </div>
    </div>
    <div class="next-form-grid">
        <div class="next-form-grid-cell">
            <label class="text-title-field" for="store_country">{{ trans('plugins/ecommerce::store-locator.country') }}</label>
            <div class="ui-select-wrapper">
                <select name="country" class="ui-select" id="store_country" data-type="country">
                    @foreach(EcommerceHelper::getAvailableCountries() as $countryCode => $countryName)
                        <option value="{{ $countryCode }}" @if (($locator ? $locator->country : null) == $countryCode) selected @endif>{{ $countryName }}</option>
                    @endforeach
                </select>
                <svg class="svg-next-icon svg-next-icon-size-16">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z"></path></svg>
                </svg>
            </div>
        </div>
    </div>
    <div class="next-form-grid">
        <div class="next-form-grid-cell">
            <label class="text-title-field" for="store_state">{{ trans('plugins/ecommerce::store-locator.state') }}</label>
            @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
                <div class="ui-select-wrapper">
                    <select name="state" class="ui-select" id="store_state" data-type="state" data-url="{{ route('ajax.states-by-country') }}">
                        <option value="">{{ __('Select state...') }}</option>
                        @if ($locator ? $locator->country : null || !EcommerceHelper::isUsingInMultipleCountries())
                            @foreach(EcommerceHelper::getAvailableStatesByCountry($locator ? $locator->country : null) as $stateId => $stateName)
                                <option value="{{ $stateId }}" @if (($locator ? $locator->state : null) == $stateId) selected @endif>{{ $stateName }}</option>
                            @endforeach
                        @endif
                    </select>
                    <svg class="svg-next-icon svg-next-icon-size-16">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z"></path></svg>
                    </svg>
                </div>
            @else
                <input type="text" class="next-input" name="state" id="store_state" value="{{ $locator ? $locator->state : null }}">
            @endif
        </div>
        <div class="next-form-grid-cell">
            <label class="text-title-field" for="store_city">{{ trans('plugins/ecommerce::store-locator.city') }}</label>
            @if (EcommerceHelper::useCityFieldAsTextField())
                <input type="text" class="next-input" name="city" id="store_city" value="{{ $locator ? $locator->city : null }}">
            @else
                <div class="ui-select-wrapper">
                    <select name="city" class="ui-select" id="store_city" data-type="city" data-using-select2="false" data-url="{{ route('ajax.cities-by-state') }}">
                        <option value="">{{ __('Select city...') }}</option>
                        @if ($locator ? $locator->state : null)
                            @foreach(EcommerceHelper::getAvailableCitiesByState($locator ? $locator->state : null) as $cityId => $cityName)
                                <option value="{{ $cityId }}" @if (($locator ? $locator->city : null) == $cityId) selected @endif>{{ $cityName }}</option>
                            @endforeach
                        @endif
                    </select>
                    <svg class="svg-next-icon svg-next-icon-size-16">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z"></path></svg>
                    </svg>
                </div>
            @endif
        </div>
    </div>
    <div class="mb-3 form-group">
        <label class="next-label">

            <input type="checkbox" value="1" name="is_shipping_location" @if (!$locator || $locator->is_shipping_location) checked @endif>

            {{ trans('plugins/ecommerce::store-locator.store_name') }}?
        </label>
    </div>
</div>
{!! Form::close() !!}
