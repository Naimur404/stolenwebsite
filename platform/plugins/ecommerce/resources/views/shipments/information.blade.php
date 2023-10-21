<div class="flexbox-content flexbox-right">
    <div class="wrapper-content">
        <div class="pd-all-20">
            <label class="title-product-main text-no-bold">{{ trans('plugins/ecommerce::shipping.shipment_information') }}</label>
        </div>
        <div class="pd-all-20 p-t15 p-b15 border-top-title-main ps-relative">
            <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding mb10">
                <div class="flexbox-grid-form-item">
                    {{ trans('plugins/ecommerce::shipping.order_number') }}
                </div>
                <div class="flexbox-grid-form-item text-end">
                    <a target="_blank" href="{{ $orderEditRouteName ? route($orderEditRouteName, $shipment->order->id) : '' }}" class="hover-underline">{{ $shipment->order->code }} <i class="fa fa-external-link-alt"></i></a>
                </div>
            </div>
            <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding mb10">
                <div class="flexbox-grid-form-item">
                    {{ trans('plugins/ecommerce::shipping.shipping_method') }}
                </div>
                <div class="flexbox-grid-form-item text-end ws-nm">
                    <label class="font-size-11px">{{ OrderHelper::getShippingMethod($shipment->order->shipping_method) }}
                        @if ($shipment->order->shipping_option)
                            ({{ $shipment->order->shipping_method_name }})
                        @endif
                    </label>
                </div>
            </div>
            <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding mb10">
                <div class="flexbox-grid-form-item">
                    {{ trans('plugins/ecommerce::shipping.shipping_fee') }}
                </div>
                <div class="flexbox-grid-form-item text-end ws-nm">
                    <label class="font-size-11px">
                        <span>{{ format_price($shipment->price) }}</span>
                    </label>
                </div>
            </div>
            @if ((float)$shipment->cod_amount)
                <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding mb10">
                    <div class="flexbox-grid-form-item">
                        {{ trans('plugins/ecommerce::shipping.cod_amount') }}:
                    </div>
                    <div class="flexbox-grid-form-item text-end ws-nm">
                        <label class="font-size-11px">
                            <span>{{ format_price($shipment->cod_amount) }}</span>
                        </label>
                    </div>
                </div>
                <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding mb10">
                    <div class="flexbox-grid-form-item">
                        {{ trans('plugins/ecommerce::shipping.cod_status') }}
                    </div>
                    <div class="flexbox-grid-form-item text-end">
                        {!! BaseHelper::clean($shipment->cod_status->toHtml()) !!}
                    </div>
                </div>
            @endif
            <div class="flexbox-grid-form flexbox-grid-form-no-outside-padding mb10">
                <div class="flexbox-grid-form-item">
                    {{ trans('plugins/ecommerce::shipping.shipping_status') }}
                </div>
                <div class="flexbox-grid-form-item text-end">
                    {!! BaseHelper::clean($shipment->status->toHtml()) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="wrapper-content mt20">
        <div class="pd-all-20">
            <label class="title-product-main text-no-bold">{{ trans('plugins/ecommerce::shipping.customer_information') }}</label>
        </div>
        <div class="pd-all-20 p-t15 p-b15 border-top-title-main ps-relative">
            <div class="form-group ws-nm mb0">
                <ul class="ws-nm text-infor-subdued shipping-address-info">
                    @include('plugins/ecommerce::orders.shipping-address.detail', ['address' => $shipment->order->address])
                </ul>
            </div>
        </div>
    </div>
</div>
