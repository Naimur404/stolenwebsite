@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        @include('plugins/ecommerce::shipments.notification')

        <div class="flexbox-grid no-pd-none">
            <div class="flexbox-content">
                @include('plugins/ecommerce::shipments.products', [
                    'productEditRouteName' => Auth::user()->hasPermission('products.edit') ? 'products.edit' : '',
                    'orderEditRouteName' => Auth::user()->hasPermission('orders.edit') ? 'orders.edit' : '',
                ])

                @include('plugins/ecommerce::shipments.form', [
                    'updateStatusRouteName' => 'ecommerce.shipments.update-status',
                    'updateCodStatusRouteName' => 'ecommerce.shipments.update-cod-status',
                ])

                @include('plugins/ecommerce::shipments.histories')
            </div>

            @include('plugins/ecommerce::shipments.information', [
                'orderEditRouteName' => Auth::user()->hasPermission('orders.edit') ? 'orders.edit' : '',
            ])
        </div>
    </div>

    <x-core-base::modal
        id="confirm-change-status-modal"
        :title="trans('plugins/ecommerce::shipping.change_status_confirm_title')"
        button-id="confirm-change-shipment-status-button"
        :button-label="trans('plugins/ecommerce::shipping.accept')"
    >
        {!! trans('plugins/ecommerce::shipping.change_status_confirm_description') !!}
    </x-core-base::modal>
@endsection
