<div class="variation-form-wrapper">
    <form action="">
        @include('plugins/ecommerce::products.partials.product-attribute-sets')

        @include('plugins/ecommerce::products.partials.general', ['product' => $product, 'originalProduct' => $originalProduct, 'isVariation' => true])
        <div class="variation-images">
            {!! Form::customImages('images', isset($product) ? $product->images : []) !!}
        </div>
    </form>
</div>
