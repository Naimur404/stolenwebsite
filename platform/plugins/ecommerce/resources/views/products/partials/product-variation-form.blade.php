<div class="variation-form-wrapper">
    <form action="">
        @include('plugins/ecommerce::products.partials.product-attribute-sets')

        @include('plugins/ecommerce::products.partials.general', ['product' => $product, 'originalProduct' => $originalProduct, 'isVariation' => true])
        <div class="variation-images">
            @include('core/base::forms.partials.images', ['name' => 'images[]', 'values' => isset($product) ? $product->images : []])
        </div>
    </form>

    @once
        <script id="gallery_select_image_template" type="text/x-custom-template">
            <div class="list-photo-hover-overlay">
                <ul class="photo-overlay-actions">
                    <li>
                        <a class="mr10 btn-trigger-edit-gallery-image" data-bs-toggle="tooltip" data-placement="bottom"
                           data-bs-original-title="{{ trans('core/base::base.change_image') }}">
                            <i class="fa fa-edit"></i>
                        </a>
                    </li>
                    <li>
                        <a class="mr10 btn-trigger-remove-gallery-image" data-bs-toggle="tooltip" data-placement="bottom"
                           data-bs-original-title="{{ trans('core/base::base.delete_image') }}">
                            <i class="fa fa-trash"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="custom-image-box image-box">
                <input type="hidden" name="__name__" class="image-data">
                <img src="{{ RvMedia::getDefaultImage() }}" alt="{{ trans('core/base::base.preview_image') }}" class="preview_image">
                <div class="image-box-actions">
                    <a class="btn-images" data-result="images[]" data-action="select-image">
                        {{ trans('core/base::forms.choose_image') }}
                    </a> |
                    <a class="btn_remove_image">
                        <span></span>
                    </a>
                </div>
            </div>
        </script>
    @endonce

</div>
