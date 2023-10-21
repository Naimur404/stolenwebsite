<div class="shipment-info-panel hide-print">
    <div class="shipment-info-header">
        @if (MarketplaceHelper::allowVendorManageShipping())
            <a target="_blank" href="{{ route('marketplace.vendor.shipments.edit', $shipment->id) }}">
                <h4>{{ get_shipment_code($shipment->id) }}</h4>
            </a>
        @else
            <h4>{{ get_shipment_code($shipment->id) }}</h4>
        @endif
        <span class="label carrier-status carrier-status-{{ $shipment->status }}">{{ $shipment->status->label() }}</span>
    </div>

    <div class="pd-all-20 pt10">
        <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding rps-form-767 pt10">
            <div class="flexbox-grid-form-item ws-nm">
                <span>{{ trans('plugins/ecommerce::shipping.shipping_method') }}: <span><i>{{ $shipment->order->shipping_method_name }}</i></span></span>
            </div>
            <div class="flexbox-grid-form-item rps-no-pd-none-r ws-nm">
                <span>{{ trans('plugins/ecommerce::shipping.weight_unit', ['unit' => ecommerce_weight_unit()]) }}:</span> <span><i>{{ $shipment->weight }} {{ ecommerce_weight_unit() }}</i></span>
            </div>
        </div>
        <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding rps-form-767 pt10">
            <div class="flexbox-grid-form-item ws-nm">
                <span>{{ trans('plugins/ecommerce::shipping.updated_at') }}:</span> <span><i>{{ $shipment->updated_at }}</i></span>
            </div>
            @if ((float)$shipment->cod_amount)
                <div class="flexbox-grid-form-item ws-nm rps-no-pd-none-r">
                    <span>{{ trans('plugins/ecommerce::shipping.cod_amount') }}:</span>
                    <span><i>{{ format_price($shipment->cod_amount) }}</i></span>
                </div>
            @endif
        </div>
        @if ($shipment->note)
            <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding rps-form-767 pt10">
                <div class="flexbox-grid-form-item ws-nm rps-no-pd-none-r">
                    <span>{{ trans('plugins/ecommerce::shipping.delivery_note') }}:</span>
                    <strong><i>{{ $shipment->note }}</i></strong>
                </div>
            </div>
        @endif
    </div>

    <div class="panel-heading order-bottom shipment-actions-wrapper">
        <div class="flexbox-grid-default">
            <div class="flexbox-content">
                @if (MarketplaceHelper::allowVendorManageShipping() ||
                    in_array($shipment->status, [
                        \Botble\Ecommerce\Enums\ShippingStatusEnum::PENDING,
                        \Botble\Ecommerce\Enums\ShippingStatusEnum::APPROVED,
                        \Botble\Ecommerce\Enums\ShippingStatusEnum::ARRANGE_SHIPMENT,
                        \Botble\Ecommerce\Enums\ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT
                    ]))
                    <button class="btn btn-info ml10 btn-trigger-update-shipping-status"><i class="fas fa-shipping-fast"></i> {{ trans('plugins/ecommerce::shipping.update_shipping_status') }}</button>
                @endif

                {!! apply_filters('shipment_buttons_detail_order', null, $shipment) !!}
            </div>
        </div>
    </div>
</div>
