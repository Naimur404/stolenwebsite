<template>
    <div class='row align-items-center'>
        <div class='col'>
            <span class='text-success' v-if='product.variation_attributes'>
                {{ product.variation_attributes }}
            </span>
            <span v-else>{{ product.name }}</span>
            <ProductAvailable v-if='product.is_variation || !product.variations.length'
                              :item='product'></ProductAvailable>
            <ProductOption v-show='!product.is_variation' :ref="'product_options_' + product.id" :product='product'
                           :options='product.product_options'></ProductOption>
        </div>
        <div class='col-auto' v-if='(product.is_variation || !product.variations.length) && !product.is_out_of_stock'>
            <button class='btn btn-outline-primary btn-sm py-1' type='button'
                    @click="$emit('select-product', product, $refs['product_options_' + product.id] || [])">
                <i class='fa fa-plus'></i>
                <span>{{ __('order.add') }}</span>
            </button>
        </div>
    </div>
</template>

<script>
import ProductAvailable from './ProductAvailableComponent.vue'
import ProductOption from './ProductOptionComponent.vue'

export default {
    props: {
        product: {
            type: Object,
            default: {},
            required: false,
        },
    },
    components: {
        ProductAvailable,
        ProductOption,
    },
}
</script>
