@php
    use Botble\Payment\Enums\PaymentMethodEnum;
    use Botble\Payment\Models\Payment;
    use Botble\Payment\Supports\PaymentHelper;
@endphp

@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="container">
        <div class="row">
            <div class="group flexbox-annotated-section">
                <div class="col-md-3">
                    <h4>{{ trans('plugins/payment::payment.payment_methods') }}</h4>
                    <p>{{ trans('plugins/payment::payment.payment_methods_description') }}</p>
                </div>
                <div class="col-md-9">
                    @php do_action(BASE_ACTION_META_BOXES, 'top', new Payment) @endphp

                    <div class="wrapper-content pd-all-20">
                        {!! Form::open(['route' => 'payments.settings']) !!}
                        <x-core-setting::select
                            name="default_payment_method"
                            :label="trans('plugins/payment::payment.default_payment_method')"
                            :options="PaymentMethodEnum::labels()"
                            :value="PaymentHelper::defaultPaymentMethod()"
                        />
                        <button type="button" class="btn btn-info button-save-payment-settings">{{ trans('core/base::forms.save') }}</button>
                        {!! Form::close() !!}
                    </div>

                    <br>

                    {!! apply_filters(PAYMENT_METHODS_SETTINGS_PAGE, null) !!}

                    <div class="table-responsive">
                        <table class="table payment-method-item">
                            <tbody>
                            <tr class="border-pay-row">
                                <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
                                <td style="width: 20%;">
                                    <span>{{ trans('plugins/payment::payment.payment_methods') }}</span>
                                </td>
                                <td class="border-right">
                                    <ul>
                                        <li>
                                            <p>{{ trans('plugins/payment::payment.payment_methods_instruction') }}</p>
                                        </li>
                                    </ul>
                                </td>
                            </tr>

                            @php $codStatus = setting('payment_cod_status'); @endphp
                            <tr class="bg-white">
                                <td colspan="3">
                                    <div class="float-start" style="margin-top: 5px;">
                                        <div class="payment-name-label-group">
                                            @if ($codStatus != 0)
                                                <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                                            @endif
                                            <label class="ws-nm inline-display method-name-label">{{ setting('payment_cod_name', PaymentMethodEnum::COD()->label()) }}</label>
                                        </div>
                                    </div>
                                    <div class="float-end">
                                        <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($codStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                                        <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($codStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
                                    </div>
                                </td>
                            </tr>
                            <tr class="payment-content-item hidden">
                                <td class="border-left" colspan="3">
                                    {!! Form::open() !!}
                                    {!! Form::hidden('type', 'cod', ['class' => 'payment_type']) !!}
                                    <div class="col-sm-12 mt-2">
                                        <div class="well bg-white">
                                            <x-core-setting::text-input
                                                name="payment_cod_name"
                                                :label="trans('plugins/payment::payment.method_name')"
                                                :value="setting('payment_cod_name', PaymentMethodEnum::COD()->label())"
                                                data-counter="400"
                                            />

                                            <x-core-setting::form-group>
                                                <label class="text-title-field" for="payment_cod_description">{{ trans('plugins/payment::payment.payment_method_description') }}</label>
                                                {!! Form::editor('payment_cod_description', setting('payment_cod_description')) !!}
                                            </x-core-setting::form-group>

                                            {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, 'cod') !!}
                                        </div>
                                    </div>
                                    <div class="col-12 bg-white text-end">
                                        <button class="btn btn-warning disable-payment-item @if ($codStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.deactivate')  }}</button>
                                        <button class="btn btn-info save-payment-item btn-text-trigger-save @if ($codStatus == 1) hidden @endif" type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                                        <button class="btn btn-info save-payment-item btn-text-trigger-update @if ($codStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.update') }}</button>
                                    </div>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                            </tbody>

                            @php $bankTransferStatus = setting('payment_bank_transfer_status'); @endphp
                            <tbody class="border-none-t">
                            <tr class="bg-white">
                                <td colspan="3">
                                    <div class="float-start" style="margin-top: 5px;">
                                        <div class="payment-name-label-group">
                                            @if ($bankTransferStatus != 0)
                                                <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                                            @endif
                                            <label class="ws-nm inline-display method-name-label">{{ setting('payment_bank_transfer_name', PaymentMethodEnum::BANK_TRANSFER()->label()) }}</label>
                                        </div>
                                    </div>
                                    <div class="float-end">
                                        <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($bankTransferStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                                        <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($bankTransferStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
                                    </div>
                                </td>
                            </tr>
                            <tr class="payment-content-item hidden">
                                <td class="border-left" colspan="3">
                                    {!! Form::open() !!}
                                    {!! Form::hidden('type', 'bank_transfer', ['class' => 'payment_type']) !!}
                                    <div class="col-sm-12 mt-2">
                                        <div class="well bg-white">
                                            <x-core-setting::text-input
                                                name="payment_bank_transfer_name"
                                                :label="trans('plugins/payment::payment.method_name')"
                                                :value="setting('payment_bank_transfer_name', PaymentMethodEnum::BANK_TRANSFER()->label())"
                                                data-counter="400"
                                            />

                                            <x-core-setting::form-group>
                                                <label class="text-title-field" for="payment_bank_transfer_description">{{ trans('plugins/payment::payment.payment_method_description') }}</label>
                                                {!! Form::editor('payment_bank_transfer_description', setting('payment_bank_transfer_description')) !!}
                                            </x-core-setting::form-group>

                                            {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, 'bank_transfer') !!}
                                        </div>
                                    </div>
                                    <div class="col-12 bg-white text-end">
                                        <button class="btn btn-warning disable-payment-item @if ($bankTransferStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                                        <button class="btn btn-info save-payment-item btn-text-trigger-save @if ($bankTransferStatus == 1) hidden @endif" type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                                        <button class="btn btn-info save-payment-item btn-text-trigger-update @if ($bankTransferStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.update') }}</button>
                                    </div>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @php do_action(BASE_ACTION_META_BOXES, 'main', new Payment) @endphp
            <div class="group">
                <div class="col-md-3"></div>
                <div class="col-md-9">
                    @php do_action(BASE_ACTION_META_BOXES, 'advanced', new Payment) @endphp
                </div>
            </div>
        </div>
    </div>

    <x-core-base::modal
        id="confirm-disable-payment-method-modal"
        :title="trans('plugins/payment::payment.deactivate_payment_method')"
        button-id="confirm-disable-payment-method-button"
        :button-label="trans('plugins/payment::payment.agree')"
    >
        {!! trans('plugins/payment::payment.deactivate_payment_method_description') !!}
    </x-core-base::modal>
@endsection
