@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <div class="group">
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <h4>{{ trans('plugins/ecommerce::shipping.shipping_rules') }}</h4>
                    <p>{{trans('plugins/ecommerce::shipping.shipping_rules_description') }}</p>
                    <p><a href="#" class="btn btn-secondary btn-select-country">{{ trans('plugins/ecommerce::shipping.select_country') }}</a></p>
                </div>
                <div class="col-md-9 col-sm-12">
                    <div class="wrapper-content">
                        <div class="table-wrap">
                            @foreach ($shipping as $shippingItem)
                                <div class="wrap-table-shipping-{{ $shippingItem->id }}">
                                    <div class="pd-all-20 p-none-b">
                                        <label class="p-none-r">{{ trans('plugins/ecommerce::shipping.country') }}: <strong>{{ Arr::get(EcommerceHelper::getAvailableCountries(), $shippingItem->title, $shippingItem->title) }}</strong></label>
                                        <a href="#" class="btn-change-link float-end pl20 btn-add-shipping-rule-trigger"
                                            data-shipping-id="{{ $shippingItem->id }}" data-country="{{ $shippingItem->country }}">{{ trans('plugins/ecommerce::shipping.add_shipping_rule') }}</a>
                                        <a href="#" class="btn-change-link float-end excerpt btn-confirm-delete-region-item-modal-trigger text-danger"
                                            data-id="{{ $shippingItem->id }}" data-name="{{ Arr::get(EcommerceHelper::getAvailableCountries(), $shippingItem->title, $shippingItem->title) }}">{{ trans('plugins/ecommerce::shipping.delete') }}</a>
                                    </div>
                                    <div class="pd-all-20 p-none-t p-b10 border-bottom">
                                        @foreach($shippingItem->rules as $rule)
                                            @include('plugins/ecommerce::shipping.rules.item', compact('rule'))
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {!! apply_filters(SHIPPING_METHODS_SETTINGS_PAGE, null) !!}
                </div>
            </div>
        </div>
    </div>
    <br>
    <x-core-base::modal
        id="confirm-delete-price-item-modal"
        :title="trans('plugins/ecommerce::shipping.delete_shipping_rate')"
        button-id="confirm-delete-price-item-button"
        :button-label="trans('plugins/ecommerce::shipping.confirm')"
        size="xs"
    >
        {!! trans('plugins/ecommerce::shipping.delete_shipping_rate_confirmation') !!}
    </x-core-base::modal>

    <x-core-base::modal
        id="confirm-delete-region-item-modal"
        :title="trans('plugins/ecommerce::shipping.delete_shipping_area')"
        button-id="confirm-delete-region-item-button"
        :button-label="trans('plugins/ecommerce::shipping.confirm')"
        size="xs"
    >
        {!! trans('plugins/ecommerce::shipping.delete_shipping_area_confirmation') !!}
    </x-core-base::modal>

    <x-core-base::modal
        id="add-shipping-rule-item-modal"
        :title="trans('plugins/ecommerce::shipping.add_shipping_fee_for_area')"
        button-id="add-shipping-rule-item-button"
        :button-label="trans('plugins/ecommerce::shipping.save')"
    >
        {!! view('plugins/ecommerce::shipping.rules.form', ['rule' => null])->render() !!}
    </x-core-base::modal>

    <div data-delete-region-item-url="{{ route('shipping_methods.region.destroy') }}"></div>
    <div data-delete-rule-item-url="{{ route('shipping_methods.region.rule.destroy') }}"></div>

    <x-core-base::modal
        id="select-country-modal"
        :title="trans('plugins/ecommerce::shipping.add_shipping_region')"
        button-id="add-shipping-region-button"
        :button-label="trans('plugins/ecommerce::shipping.save')"
        size="xs"
    >
        {!! FormBuilder::create(\Botble\Ecommerce\Forms\AddShippingRegionForm::class)->renderForm() !!}
    </x-core-base::modal>

    <x-core-base::modal
        id="form-shipping-rule-item-detail-modal"
        :title="trans('plugins/ecommerce::shipping.add_shipping_region')"
        button-id="save-shipping-rule-item-detail-button"
        :button-label="trans('plugins/ecommerce::shipping.save')"
    >
    </x-core-base::modal>

    <x-core-base::modal
        id="confirm-delete-shipping-rule-item-modal"
        :title="trans('plugins/ecommerce::shipping.rule.item.delete')"
        button-id="confirm-delete-shipping-rule-item-button"
        :button-label="trans('plugins/ecommerce::shipping.confirm')"
        size="xs"
    >
        {!! trans('plugins/ecommerce::shipping.rule.item.confirmation') !!}
    </x-core-base::modal>
@endsection
