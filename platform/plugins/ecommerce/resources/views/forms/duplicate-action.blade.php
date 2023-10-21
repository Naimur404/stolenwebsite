&nbsp;
<button type="button" class="btn btn-warning btn-trigger-duplicate-product" data-url="{{ route('products.duplicate', $product->getKey()) }}">
    <i class="fa fa-copy"></i> {{ trans('plugins/ecommerce::ecommerce.forms.duplicate') }}
</button>

@push('footer')
    <x-core-base::modal
        id="duplicate-product-modal"
        :title="trans('plugins/ecommerce::ecommerce.duplicate_modal')"
        type="info"
        button-id="confirm-duplicate-product-button"
        :button-label="trans('plugins/ecommerce::ecommerce.forms.duplicate')"
    >
        {{ trans('plugins/ecommerce::ecommerce.duplicate_modal_description') }}
    </x-core-base::modal>
@endpush
