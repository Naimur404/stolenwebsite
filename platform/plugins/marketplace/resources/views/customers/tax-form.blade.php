<div class="tab-pane" id="tab_tax_info">
    <div class="form-group">
        <div class="ps-form__content">
           <div class="form-group">
                <label for="tax_info_business_name">{{ __('Business Name') }}:</label>
                <input id="tax_info_business_name"
                       type="text"
                       class="form-control"
                       name="tax_info[business_name]"
                       placeholder="{{ __('Business Name') }}"
                       value="{{ Arr::get($model->tax_info, 'business_name') }}">
            </div>
            {!! Form::error('tax_info[business_name]', $errors) !!}

            <div class="form-group">
                <label for="tax_info_tax_id">{{ __('Tax ID') }}:</label>
                <input id="tax_info_tax_id"
                       type="text"
                       class="form-control"
                       name="tax_info[tax_id]"
                       placeholder="{{ __('Tax ID') }}"
                       value="{{ Arr::get($model->tax_info, 'tax_id') }}">
            </div>
            {!! Form::error('tax_info[tax_id]', $errors) !!}

            <div class="form-group">
                <label for="tax_info_address">{{ __('Address') }}:</label>
                <input id="tax_info_address"
                       type="text"
                       class="form-control"
                       name="tax_info[address]"
                       placeholder="{{ __('Address') }}"
                       value="{{ Arr::get($model->tax_info, 'address') }}">
            </div>
            {!! Form::error('tax_info[address]', $errors) !!}

        </div>
    </div>
</div>
