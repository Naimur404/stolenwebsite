<div id="extra-shipment-info" class="widget meta-boxes">
    <div class="widget-title">
        <h4><span>{{ trans('plugins/ecommerce::shipping.additional_shipment_information') }}</span></h4>
    </div>
    <div class="widget-body">
        {!! app(\Botble\Base\Forms\FormBuilder::class)
            ->create(\Botble\Ecommerce\Forms\ShipmentInfoForm::class, ['model' => $shipment])
            ->renderForm() !!}
    </div>
</div>

@if (! $shipment->isCancelled)
    <div class="shipment-actions d-inline-block">
        <div class="dropdown btn-group">
            <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="mr5">{{ trans('plugins/ecommerce::shipping.update_shipping_status') }}</span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                @foreach(\Botble\Ecommerce\Enums\ShippingStatusEnum::values() as $item)
                    <li>
                        <a data-value="{{ $item->getValue() }}" data-target="{{ route($updateStatusRouteName, $shipment->id) }}">{{ $item->label() }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        @if ((float)$shipment->cod_amount)
            <div class="dropdown btn-group p-l10">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="mr5">{{ trans('plugins/ecommerce::shipping.update_cod_status') }}</span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    @foreach(\Botble\Ecommerce\Enums\ShippingCodStatusEnum::values() as $item)
                        <li><a data-value="{{ $item->getValue() }}" data-target="{{ route($updateCodStatusRouteName, $shipment->id) }}">{{ $item->label() }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
