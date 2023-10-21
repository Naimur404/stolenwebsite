@extends('core/base::forms.form-tabs')

@section('form_end')
    <x-core-base::modal
        id="add-address-modal"
        :title="trans('plugins/ecommerce::addresses.add_address')"
        button-id="confirm-add-address-button"
        :button-label="trans('plugins/ecommerce::addresses.add')"
        size="md"
    >
        <form action="{{ route('customers.addresses.create.store') }}" method="POST">
            <input type="hidden" name="customer_id" value="{{ $form->getModel()->id }}">

            @include('plugins/ecommerce::customers.addresses.form', ['address' => new \Botble\Ecommerce\Models\Address()])
        </form>
    </x-core-base::modal>

    <x-core-base::modal
        id="edit-address-modal"
        :title="trans('plugins/ecommerce::addresses.edit_address')"
        button-id="confirm-edit-address-button"
        :button-label="trans('plugins/ecommerce::addresses.save')"
        size="md"
    >
        <div class="modal-loading-block d-none">
            @include('core/base::elements.loading')
        </div>

        <div class="modal-form-content"></div>
    </x-core-base::modal>

    @include('core/table::partials.modal-item', [
        'type' => 'danger',
        'name' => 'modal-confirm-delete',
        'title' => trans('core/base::tables.confirm_delete'),
        'content' => trans('core/base::tables.confirm_delete_msg'),
        'action_name' => trans('core/base::tables.delete'),
        'action_button_attributes' => [
            'class' => 'delete-crud-entry',
        ],
    ])
@endsection

@section('form_main_end')
    @if ($customerId = $form->getModel()->id)
        <div class="customer-reviews-table widget meta-boxes">
            <div class="widget-title">
                <h4>
                    <span>{{ trans('plugins/ecommerce::review.name') }}</span>
                </h4>
            </div>
            <div class="widget-body">
                {!! app(\Botble\Ecommerce\Tables\CustomerReviewTable::class)
                    ->customerId($customerId)
                    ->setAjaxUrl(route('customers.ajax.reviews', $customerId))
                    ->renderTable()
                !!}
            </div>
        </div>
    @endif
@endsection
