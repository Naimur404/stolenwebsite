@extends(MarketplaceHelper::viewPath('dashboard.layouts.master'))

@section('content')
    {!! Form::open(['id' => 'marketplace-vendor-discount']) !!}
        <div id="main-discount">
            <div class="max-width-1200">
                <discount-vendor-component
                    currency="{{ get_application_currency()->symbol }}"
                    generate-url={{ route('marketplace.vendor.discounts.generate-coupon') }}
                    cancel-url={{ route('marketplace.vendor.discounts.index') }}
                     date-format="{{ config('core.base.general.date_format.date') }}"
                    >
                </discount-vendor-component>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@push('pre-footer')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans.discount = JSON.parse('{!! addslashes(json_encode(trans('plugins/ecommerce::discount'))) !!}');

        window.trans.enums = {
            'typeOptions': {!! json_encode(MarketplaceHelper::discountTypes()) !!}
        };
    </script>
    @php
        Assets::addScripts(['form-validation']);
    @endphp
    {!! JsValidator::formRequest(\Botble\Ecommerce\Http\Requests\DiscountRequest::class, '#marketplace-vendor-discount') !!}
@endpush
