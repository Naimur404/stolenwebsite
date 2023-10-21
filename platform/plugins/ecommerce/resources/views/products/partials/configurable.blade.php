<div id="product-variations-wrapper">
    <div class="variation-actions">
        <a href="#" class="btn-trigger-delete-selected-variations text-danger" style="display: none" data-target="{{ route('products.delete-versions') }}">{{ trans('plugins/ecommerce::products.delete_selected_variations') }}</a>
        <a href="#" class="btn-trigger-select-product-attributes" data-target="{{ route('products.store-related-attributes', $product->id) }}">{{ trans('plugins/ecommerce::products.edit_attribute') }}</a>
        <a href="#" class="btn-trigger-generate-all-versions" data-target="{{ route('products.generate-all-versions', $product->id) }}">{{ trans('plugins/ecommerce::products.generate_all_variations') }}</a>
    </div>

    {!! $productVariationTable->renderTable() !!}

    <br>
    <a href="#" class="btn-trigger-add-new-product-variation"
       data-target="{{ route('products.add-version', $product->id) }}"
       data-load-form="{{ route('products.get-version-form', ['id' => 0, 'product_id' => $product->id]) }}"
       data-processing="{{ trans('plugins/ecommerce::products.processing') }}"
    >{{ trans('plugins/ecommerce::products.add_new_variation') }}</a>
</div>

<x-core-base::modal
    id="select-attribute-sets-modal"
    :title="trans('plugins/ecommerce::products.select_attribute')"
    button-id="store-related-attributes-button"
    :button-label="trans('plugins/ecommerce::products.save_changes')"
>
    {!! view('plugins/ecommerce::products.partials.attribute-sets', compact('productAttributeSets'))->render() !!}
</x-core-base::modal>

@push('footer')
    <x-core-base::modal
        id="add-new-product-variation-modal"
        :title="trans('plugins/ecommerce::products.add_new_variation')"
        button-id="store-product-variation-button"
        :button-label="trans('plugins/ecommerce::products.save_changes')"
        size="lg"
    >
        {!! view('core/base::elements.loading')->render() !!}
    </x-core-base::modal>

    <x-core-base::modal
        id="edit-product-variation-modal"
        :title="trans('plugins/ecommerce::products.add_new_variation')"
        button-id="update-product-variation-button"
        :button-label="trans('plugins/ecommerce::products.save_changes')"
        size="lg"
    >
        {!! view('core/base::elements.loading')->render() !!}
    </x-core-base::modal>

    <x-core-base::modal
        id="generate-all-versions-modal"
        :title="trans('plugins/ecommerce::products.generate_all_variations')"
        button-id="generate-all-versions-button"
        :button-label="trans('plugins/ecommerce::products.continue')"
    >
        {{ trans('plugins/ecommerce::products.generate_all_variations_confirmation') }}
    </x-core-base::modal>

    <x-core-base::modal
        id="confirm-delete-version-modal"
        :title="trans('plugins/ecommerce::products.delete_variation')"
        type="danger"
        button-id="delete-version-button"
        :button-label="trans('plugins/ecommerce::products.continue')"
    >
        {{ trans('plugins/ecommerce::products.delete_variation_confirmation') }}
    </x-core-base::modal>

    <x-core-base::modal
        id="delete-variations-modal"
        :title="trans('plugins/ecommerce::products.delete_variations')"
        type="danger"
        button-id="delete-selected-variations-button"
        :button-label="trans('plugins/ecommerce::products.continue')"
    >
        {{ trans('plugins/ecommerce::products.delete_variations_confirmation') }}
    </x-core-base::modal>
@endpush
