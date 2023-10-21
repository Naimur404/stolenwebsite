@php
    $status = setting('shipping_shippo_status', 0);
    $testKey = setting('shipping_shippo_test_key') ?: '';
    $prodKey = setting('shipping_shippo_production_key') ?: '';
    $test = setting('shipping_shippo_sandbox', 1) ?: 0;
    $logging = setting('shipping_shippo_logging', 1) ?: 0;
    $cacheResponse = setting('shipping_shippo_cache_response', 1) ?: 0;
    $webhook = setting('shipping_shippo_webhooks', 1) ?: 0;
@endphp

<table class="table mt-4 bg-white">
    <tbody>
        <tr class="border-pay-row">
            <td class="border-pay-col">
                <i class="fas fa-shipping-fast"></i>
            </td>
            <td style="width: 20%;">
                <img class="filter-black" src="{{ url('vendor/core/plugins/shippo/images/logo-dark.svg') }}" alt="Shippo">
            </td>
            <td class="border-right">
                <ul>
                    <li>
                        <a href="https://goshippo.com/" target="_blank">Shippo</a>
                        <p>{{ trans('plugins/shippo::shippo.description') }}</p>
                    </li>
                </ul>
            </td>
        </tr>
        <tr class="bg-white">
            <td colspan="3">
                <div class="float-start" style="margin-top: 5px;">
                    <div class="payment-name-label-group  @if ($status == 0) d-none @endif">
                        <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                        <label class="ws-nm inline-display method-name-label">Shippo</label>
                    </div>
                </div>
                <div class="float-end">
                    <a class="btn btn-secondary" data-bs-toggle="collapse"
                        href="#collapse-shipping-method-shippo" role="button" aria-expanded="false"
                        aria-controls="collapse-shipping-method-shippo">
                        @if ($status == 0) {{ trans('core/base::forms.edit') }} @else {{ trans('core/base::layouts.settings') }} @endif
                    </a>
                </div>
            </td>
        </tr>
        <tr class="collapse" id="collapse-shipping-method-shippo">
            <td class="border-left" colspan="3">
                {!! Form::open(['route' => 'ecommerce.shipments.shippo.settings.update']) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <ul>

                                <li>
                                    <div class="alert alert-warning">
                                        <h5 class="text-danger">{{ trans('plugins/shippo::shippo.note_0') }}</h5>
                                        <ul class="ps-3">
                                            <li style="list-style-type: circle;">
                                                <span>{!! BaseHelper::clean(trans('plugins/shippo::shippo.note_1', ['link' => 'https://docs.botble.com/farmart/1.x/usage-location'])) !!}</span>
                                            </li>
                                            <li style="list-style-type: circle;">
                                                <span>{{ trans('plugins/shippo::shippo.note_2') }}</span>
                                            </li>
                                            <li style="list-style-type: circle;">
                                                <span>{!! BaseHelper::clean(trans('plugins/shippo::shippo.note_3', ['link' => route('ecommerce.settings')])) !!}</span>
                                            </li>
                                            <li style="list-style-type: circle;">
                                                <span>{!! BaseHelper::clean(trans('plugins/shippo::shippo.note_6', ['link' => 'https://goshippo.com/docs/reference#parcels-extras'])) !!}</span>
                                            </li>
                                            @if (is_plugin_active('marketplace'))
                                                <li style="list-style-type: circle;">
                                                    <span>{{ trans('plugins/shippo::shippo.note_4') }}</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </li>
                                <li>
                                    <label>{{ trans('plugins/shippo::shippo.configuration_instruction', ['name' => 'Shippo']) }}</label>
                                </li>
                                <li class="text-secondary">
                                    <p>{{ trans('plugins/shippo::shippo.configuration_requirement', ['name' => 'Shippo']) }}:</p>
                                    <ul class="ms-3 ps-2">
                                        <li style="list-style-type: decimal">
                                            <p>
                                                <a href="https://apps.goshippo.com/join" target="_blank">
                                                    {{ trans('plugins/shippo::shippo.service_registration', ['name' => 'Shippo']) }}
                                                </a>
                                            </p>
                                        </li>
                                        <li style="list-style-type: decimal">
                                            <p>{{ trans('plugins/shippo::shippo.after_service_registration_msg', ['name' => 'Shippo']) }}</p>
                                        </li>
                                        <li style="list-style-type: decimal">
                                            <p>{{ trans('plugins/shippo::shippo.enter_api_key') }}</p>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <div class="well">
                                <p class="text-secondary">
                                    {{ trans('plugins/shippo::shippo.please_provide_information') }}
                                    <a target="_blank" href="https://goshippo.com/">Shippo</a>:
                                </p>
                                <x-core-setting::text-input
                                    name="shipping_shippo_test_key"
                                    :label="trans('plugins/shippo::shippo.test_api_token')"
                                    placeholder="<API-KEY>"
                                    :disabled="BaseHelper::hasDemoModeEnabled()"
                                    :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($testKey, '*', 10) : $testKey"
                                />

                                <x-core-setting::text-input
                                    name="shipping_shippo_production_key"
                                    :label="trans('plugins/shippo::shippo.live_api_token')"
                                    placeholder="<API-KEY>"
                                    :disabled="BaseHelper::hasDemoModeEnabled()"
                                    :value="BaseHelper::hasDemoModeEnabled() ? Str::mask($prodKey, '*', 10) : $prodKey"
                                />

                                <div class="form-group mb-3">
                                    <label class="control-label" for="shipping_shippo_sandbox">
                                        {!! Form::onOff('shipping_shippo_sandbox', $test) !!}
                                        {{ trans('plugins/shippo::shippo.sandbox_mode') }}
                                    </label>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="control-label" for="shipping_shippo_status">
                                        {!! Form::onOff('shipping_shippo_status', $status) !!}
                                        {{ trans('plugins/shippo::shippo.activate') }}
                                    </label>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="control-label" for="shipping_shippo_logging">
                                        {!! Form::onOff('shipping_shippo_logging', $logging) !!}
                                        {{ trans('plugins/shippo::shippo.logging') }}
                                    </label>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="control-label" for="shipping_shippo_cache_response">
                                        {!! Form::onOff('shipping_shippo_cache_response', $cacheResponse) !!}
                                        {{ trans('plugins/shippo::shippo.cache_response') }}
                                    </label>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="control-label" for="shipping_shippo_webhooks">
                                        {!! Form::onOff('shipping_shippo_webhooks', $webhook) !!}
                                        {{ trans('plugins/shippo::shippo.webhooks') }}
                                    </label>
                                    <div class="help-block">
                                        <a href="https://goshippo.com/docs/webhooks" target="_blank" rel="noopener noreferrer" class="text-warning fw-bold">
                                            <span>Webhooks</span>
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <div>URL: <i>{{ route('shippo.webhooks', ['_token' => '__API_TOKEN__']) }}</i></div>
                                    </div>
                                </div>

                                <x-core-setting::checkbox
                                    name="shipping_shippo_validate"
                                    :label="trans('plugins/shippo::shippo.check_validate_token')"
                                    :checked="setting('shipping_shippo_validate')"
                                />

                                @if (count($logFiles))
                                    <div class="form-group mb-3">
                                        <p class="mb-0">{{ __('Log files') }}: </p>
                                        <ul>
                                            @foreach($logFiles as $logFile)
                                                <li><a href="{{ route('ecommerce.shipments.shippo.view-log', $logFile) }}" target="_blank"><strong>- {{ $logFile }} <i class="fa fa-external-link"></i></strong></a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12 mb-3">
                                <div class="note note-warning">
                                    <strong>{{ trans('plugins/shippo::shippo.not_available_in_cod_payment_method') }}</strong>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                @env('demo')
                                    <div class="note note-danger">
                                        <strong>{{ trans('plugins/shippo::shippo.disabled_in_demo_mode') }}</strong>
                                    </div>
                                @else
                                    <div class="text-end">
                                        <button class="btn btn-info" type="submit">{{ trans('core/base::forms.update') }}</button>
                                    </div>
                                @endenv
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </td>
        </tr>
    </tbody>
</table>
