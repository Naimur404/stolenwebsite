@if (!$isApproved)
    <div class="note note-warning approve-product-warning">
        <p>{!! BaseHelper::clean(trans('plugins/marketplace::store.product_approval_notification', [
            'vendor'       => Html::link($product->createdBy->store->url, $product->createdBy->store->name, ['target' => '_blank']),
            'approve_link' => Html::link(route('products.approve-product', $product->id), trans('plugins/marketplace::store.approve_here'), ['class' => 'approve-product-for-selling-button']),
        ])) !!}</p>
    </div>
@else
    <div class="note note-info approved-product-info">
        <p>{!! BaseHelper::clean(trans('plugins/marketplace::store.product_approved_notification', [
            'vendor' => Html::link($product->createdBy->store->url, $product->createdBy->store->name, ['target' => '_blank']),
            'user'   => $product->approvedBy->name,
        ])) !!}</p>
    </div>
@endif

@push('footer')
    @if (!$isApproved)
        <x-core-base::modal
            id="approve-product-for-selling-modal"
            :title="trans('plugins/marketplace::store.approve_product_confirmation')"
            type="warning"
            button-id="confirm-approve-product-for-selling-button"
            :button-label="trans('plugins/marketplace::store.approve')"
        >
            {!! trans('plugins/marketplace::store.approve_product_confirmation_description', ['vendor' => Html::link($product->createdBy->store->url, $product->createdBy->store->name, ['target' => '_blank'])]) !!}
        </x-core-base::modal>
    @endif
@endpush
