@php $payPalStatus = setting('payment_paypal_status'); @endphp
<table class="table payment-method-item">
    <tbody>
    <tr class="border-pay-row">
        <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
        <td style="width: 20%;">
            <img class="filter-black" src="{{ url('vendor/core/plugins/paypal/images/paypal.svg') }}" alt="PayPal">
        </td>
        <td class="border-right">
            <ul>
                <li>
                    <a href="https://paypal.com" target="_blank">PayPal</a>
                    <p>{{ trans('plugins/payment::payment.paypal_description') }}</p>
                </li>
            </ul>
        </td>
    </tr>
    <tr class="bg-white">
        <td colspan="3">
            <div class="float-start" style="margin-top: 5px;">
                <div class="payment-name-label-group  @if ($payPalStatus== 0) hidden @endif">
                    <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span> <label class="ws-nm inline-display method-name-label">{{ setting('payment_paypal_name') }}</label>
                </div>
            </div>
            <div class="float-end">
                <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($payPalStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($payPalStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
            </div>
        </td>
    </tr>
    <tr class="payment-content-item hidden">
        <td class="border-left" colspan="3">
            {!! Form::open() !!}
            {!! Form::hidden('type', PAYPAL_PAYMENT_METHOD_NAME, ['class' => 'payment_type']) !!}
            <div class="row">
                <div class="col-sm-6">
                    <ul>
                        <li>
                            <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => 'PayPal']) }}</label>
                        </li>
                        <li class="payment-note">
                            <p>{{ trans('plugins/payment::payment.configuration_requirement', ['name' => 'PayPal']) }}:</p>
                            <ul class="m-md-l" style="list-style-type:decimal">
                                <li style="list-style-type:decimal">
                                    <a href="https://www.paypal.com/vn/merchantsignup/applicationChecklist?signupType=CREATE_NEW_ACCOUNT&amp;productIntentId=email_payments" target="_blank">
                                        {{ trans('plugins/payment::payment.service_registration', ['name' => 'PayPal']) }}
                                    </a>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ trans('plugins/payment::payment.after_service_registration_msg', ['name' => 'PayPal']) }}</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ trans('plugins/payment::payment.enter_client_id_and_secret') }}</p>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <div class="well bg-white">
                        <x-core-setting::text-input
                            name="payment_paypal_name"
                            :label="trans('plugins/payment::payment.method_name')"
                            :value="setting('payment_paypal_name', trans('plugins/payment::payment.pay_online_via', ['name' => 'PayPal']))"
                            data-counter="400"
                        />

                        <div class="form-group mb-3">
                            <label class="text-title-field" for="payment_paypal_description">{{ trans('core/base::forms.description') }}</label>
                            <textarea class="next-input" name="payment_paypal_description" id="payment_paypal_description">{{ get_payment_setting('description', 'paypal', __('You will be redirected to :name to complete the payment.', ['name' => 'PayPal'])) }}</textarea>
                        </div>

                        <p class="payment-note">
                            {{ trans('plugins/payment::payment.please_provide_information') }} <a target="_blank" href="//www.paypal.com">PayPal</a>:
                        </p>

                        <x-core-setting::text-input
                            name="payment_paypal_client_id"
                            :label="trans('plugins/payment::payment.client_id')"
                            :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' :setting('payment_paypal_client_id')"
                        />

                        <x-core-setting::text-input
                            :name="'payment_' . PAYPAL_PAYMENT_METHOD_NAME . '_client_secret'"
                            type="password"
                            :label="trans('plugins/payment::payment.client_secret')"
                            :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : setting('payment_paypal_client_secret')"
                        />

                        <x-core-setting::checkbox
                            :name="'payment_' . PAYPAL_PAYMENT_METHOD_NAME . '_mode'"
                            :label="trans('plugins/payment::payment.sandbox_mode')"
                            :value="0"
                            :checked="! get_payment_setting('mode', PAYPAL_PAYMENT_METHOD_NAME, true)"
                        />

                        {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, 'paypal') !!}
                    </div>
                </div>
            </div>
            <div class="col-12 bg-white text-end">
                <button class="btn btn-warning disable-payment-item @if ($payPalStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                <button class="btn btn-info save-payment-item btn-text-trigger-save @if ($payPalStatus == 1) hidden @endif" type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                <button class="btn btn-info save-payment-item btn-text-trigger-update @if ($payPalStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.update') }}</button>
            </div>
            {!! Form::close() !!}
        </td>
    </tr>
    </tbody>
</table>
