@php
    $payoutMethodsEnabled = \Botble\Marketplace\Enums\PayoutPaymentMethodsEnum::payoutMethodsEnabled();
@endphp
<div class="tab-pane" id="tab_payout_info">
    <div class="form-group">
        <div class="ps-form__content">
            <div class="form-group">
                <label for="bank_info_name">{{ __('Payment Method') }}:</label>
                {!! Form::customSelect('payout_payment_method', Arr::pluck($payoutMethodsEnabled, 'label', 'key'), $model->vendorInfo->payout_payment_method) !!}
                {!! Form::error('payout_payment_method', $errors) !!}
            </div>

            {!! Form::error('bank_info', $errors) !!}

            @foreach ($payoutMethodsEnabled as $method)
                <div id="payout-payment-{{ $method['key'] }}"
                    @class([
                        'payout-payment-wrapper',
                        'd-none' => old('payout_payment_method', $model->vendorInfo->payout_payment_method ?: \Botble\Marketplace\Enums\PayoutPaymentMethodsEnum::BANK_TRANSFER) != $method['key']
                    ])>
                    {!! Form::error('bank_info.' . $method['key'], $errors) !!}

                    @foreach ($method['fields'] as $key => $field)
                        <div class="form-group">
                            <label for="bank_info_{{ $method['key'] . $key }}">{{ $field['title'] }}:</label>
                            <input id="bank_info_{{ $method['key'] . $key }}"
                                type="text"
                                class="form-control"
                                name="bank_info[{{ $method['key'] }}][{{ $key }}]"
                                placeholder="{{ $field['title'] }}"
                                value="{{ old('bank_info.' . $method['key'] . '.' . $key, Arr::get($model->bank_info, $key)) }}">
                            {!! Form::error('bank_info.' . $method['key'] . '.' . $key, $errors) !!}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    'use strict';

    $(document).ready(function () {
        $(document).on('change', 'select[name="payout_payment_method"]', function () {
            $('.payout-payment-wrapper').addClass('d-none');
            $('#payout-payment-' + $(this).val()).removeClass('d-none');
        });
    });
</script>
