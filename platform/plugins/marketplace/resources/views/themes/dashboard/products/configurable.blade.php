<div id="product-variations-wrapper" class="page-content">
    <div class="variation-actions">
        <a href="#" class="btn-trigger-select-product-attributes" data-target="{{ route('marketplace.vendor.products.store-related-attributes', $product->id) }}">{{ trans('plugins/ecommerce::products.edit_attribute') }}</a>
        <a href="#" class="btn-trigger-generate-all-versions" data-target="{{ route('marketplace.vendor.products.generate-all-versions', $product->id) }}">{{ trans('plugins/ecommerce::products.generate_all_variations') }}</a>
    </div>

    {!! $productVariationTable->renderTable() !!}

    <br>
    <a href="#" class="btn-trigger-add-new-product-variation"
       data-target="{{ route('marketplace.vendor.products.add-version', $product->id) }}"
       data-load-form="{{ route('marketplace.vendor.products.get-version-form', ['id' => 0, 'product_id' => $product->id]) }}"
       data-processing="{{ trans('plugins/ecommerce::products.processing') }}"
    >{{ trans('plugins/ecommerce::products.add_new_variation') }}</a>

    <x-core-base::modal
        id="select-attribute-sets-modal"
        :title="trans('plugins/ecommerce::products.select_attribute')"
        button-id="store-related-attributes-button"
        :button-label="trans('plugins/ecommerce::products.save_changes')"
    >
        @include('plugins/ecommerce::products.partials.attribute-sets', compact('productAttributeSets'))
    </x-core-base::modal>

    <x-core-base::modal
        id="add-new-product-variation-modal"
        :title="trans('plugins/ecommerce::products.add_new_variation')"
        button-id="store-product-variation-button"
        :button-label="trans('plugins/ecommerce::products.save_changes')"
        size="lg"
    >
        @include('core/base::elements.loading')
    </x-core-base::modal>

    <x-core-base::modal
        id="edit-product-variation-modal"
        :title="trans('plugins/ecommerce::products.edit_variation')"
        button-id="update-product-variation-button"
        :button-label="trans('plugins/ecommerce::products.save_changes')"
        size="lg"
    >
        @include('core/base::elements.loading')
    </x-core-base::modal>

    <x-core-base::modal
        id="generate-all-versions-modal"
        :title="trans('plugins/ecommerce::products.generate_all_variations')"
        button-id="generate-all-versions-button"
        :button-label="trans('plugins/ecommerce::products.continue')"
    >
        {!! trans('plugins/ecommerce::products.generate_all_variations_confirmation') !!}
    </x-core-base::modal>

    <x-core-base::modal
        id="confirm-delete-version-modal"
        :title="trans('plugins/ecommerce::products.delete_variation')"
        type="danger"
        button-id="delete-version-button"
        :button-label="trans('plugins/ecommerce::products.continue')"
    >
        {!! trans('plugins/ecommerce::products.delete_variation_confirmation') !!}
    </x-core-base::modal>
</div>
