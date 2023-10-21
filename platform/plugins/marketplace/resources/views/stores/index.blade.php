@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<div class="row">
    <div class="col-md-3 right-sidebar">
        <div class="widget meta-boxes">
            <div class="widget-title">
                <h4><label for="status" class="control-label" aria-required="true">{{ trans('plugins/marketplace::revenue.store_information') }}</label></h4>
            </div>
            <div class="widget-body">
                <div class="form-group mb-3">
                    <div class="border-bottom py-2">
                        <div class="text-center">
                            <div class="text-center">
                                <img src="{{ RvMedia::getImageUrl($store->logo, 'thumb', false, RvMedia::getDefaultImage()) }}" width="120" class="mb-2" style="border-radius: 50%" alt="avatar" />
                            </div>
                            <div class="text-center">
                                <strong>
                                    <a href="{{ $store->url }}" target="_blank">{{ $store->name }} <i class="fas fa-external-link-alt"></i></a>
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="py-2">
                        <span>{{ trans('plugins/marketplace::revenue.vendor_name') }}:</span>
                        <strong><a href="{{ route('customers.edit', $customer->id) }}" target="_blank">{{ $customer->name }} <i class="fas fa-external-link-alt"></i></a></strong>
                    </div>
                    <div class="py-2">
                        <span>{{ trans('plugins/marketplace::revenue.balance') }}:</span>
                        <strong class="vendor-balance">{{ format_price($customer->balance) }} <a href="#" data-bs-toggle="modal" data-bs-target="#update-balance-modal"><i class="fa fa-edit"></i></a> </strong>
                    </div>
                    <div>
                        <span>{{ trans('plugins/marketplace::revenue.products') }}:</span>
                        <strong>{{ number_format($store->products()->count()) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="widget meta-boxes">
            <div class="widget-title">
                <h4><label for="status" class="control-label" aria-required="true">{{ trans('plugins/marketplace::revenue.statements') }}</label></h4>
                <a href="#" class="me-2 d-inline-block float-end" data-bs-toggle="modal" data-bs-target="#update-balance-modal">
                    <small><i class="fa fa-edit"></i> {{ trans('plugins/marketplace::revenue.update_balance') }}</small>
                </a>
            </div>
            <div class="widget-body">
                {!! $table->renderTable() !!}
            </div>
        </div>
    </div>

    <x-core-base::modal
        id="update-balance-modal"
        :title="trans('plugins/marketplace::revenue.update_balance_title')"
        button-id="confirm-update-amount-button"
        :button-label="trans('core/base::tables.submit')"
        size="md"
    >
        {!! Form::open(['url' => route('marketplace.store.revenue.create', $store->id)]) !!}
            <div class="form-group mb-3">
                <label class="control-label required" for="amount">{{ trans('plugins/marketplace::revenue.forms.amount') . ' (' . get_application_currency()->symbol . ')' }}</label>
                <input type="number" class="form-control" name="amount" id="amount" placeholder="{{ trans('plugins/marketplace::revenue.forms.amount_placeholder') }}">
            </div>
            <div class="form-group mb-3">
                <label class="control-label required" for="type">{{ trans('plugins/marketplace::revenue.forms.type') }}</label>
                {!! Form::customSelect('type', Botble\Marketplace\Enums\RevenueTypeEnum::adjustLabels()) !!}
            </div>
            <div class="form-group mb-3">
                <label class="control-label" for="description">{{ trans('core/base::forms.description') }}</label>
                <textarea class="form-control" name="description" id="description" placeholder="{{ trans('plugins/marketplace::revenue.forms.description_placeholder') }}" rows="5"></textarea>
            </div>
        {!! Form::close() !!}
    </x-core-base::modal>
</div>
@stop
