<div class="customer-tax-information-form">
    <div class="form-group mb-3">
        <input type="checkbox" name="with_tax_information" value="1" id="with_tax_information" @checked (old('with_tax_information', Arr::get($sessionCheckoutData, 'with_tax_information', false)))>
        <label for="with_tax_information" class="control-label">{{ __("Requires company invoice (Please fill in your company information to receive the invoice)?") }}</label>
    </div>

    <div class="tax-information-form-wrapper" @style(['display: none' => ! Arr::get($sessionCheckoutData, 'with_tax_information', false)])>
        <div @class(['form-group mb-3', 'has-error' => $errors->has('tax_information.company_name')])>
            <div class="form-input-wrapper">
                <input type="text" name="tax_information[company_name]" id="tax-information-company-name" class="form-control" value="{{ old('tax_information.company_name', Arr::get($sessionCheckoutData, 'tax_information.company_name')) }}">
                <label for='tax-information-company-name'>{{ __('Company name') }}</label>
            </div>
            {!! Form::error('tax_information.company_name', $errors) !!}
        </div>

        <div @class(['form-group mb-3', 'has-error' => $errors->has('tax_information.company_address')])>
            <div class="form-input-wrapper">
                <input type="text" name="tax_information[company_address]" id="tax-information-company-address" class="form-control" value="{{ old('tax_information.company_address', Arr::get($sessionCheckoutData, 'tax_information.company_address')) }}">
                <label for='tax-information-company-address'>{{ __('Company address') }}</label>
            </div>
            {!! Form::error('tax_information.company_address', $errors) !!}
        </div>

        <div @class(['form-group mb-3', 'has-error' => $errors->has('tax_information.company_tax_code')])>
            <div class="form-input-wrapper">
                <input type="text" name="tax_information[company_tax_code]" id="tax-information-company-tax-code" class="form-control" value="{{ old('tax_information.company_tax_code', Arr::get($sessionCheckoutData, 'tax_information.company_tax_code')) }}">
                <label for='tax-information-company-tax-code'>{{ __('Company tax code') }}</label>
            </div>
            {!! Form::error('tax_information.company_tax_code', $errors) !!}
        </div>

        <div @class(['form-group mb-3', 'has-error' => $errors->has('tax_information.company_email')])>
            <div class="form-input-wrapper">
                <input type="email" name="tax_information[company_email]" id="tax-information-company-email" class="form-control" value="{{ old('tax_information.company_email', Arr::get($sessionCheckoutData, 'tax_information.company_email')) }}">
                <label for='tax-information-company-email'>{{ __('Company email') }}</label>
            </div>
            {!! Form::error('tax_information.company_email', $errors) !!}
        </div>
    </div>
</div>
