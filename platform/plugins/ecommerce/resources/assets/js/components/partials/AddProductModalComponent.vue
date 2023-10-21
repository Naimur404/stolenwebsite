<template>
    <div>
        <ec-modal id='add-product-item' :title="__('order.add_product')" :ok-title="__('order.save')"
                 :cancel-title="__('order.cancel')"
                 @shown='resetProductData()' @ok="$emit('create-product', $event, product)">
            <div class='form-group mb15'>
                <label class='text-title-field'>{{ __('order.name') }}</label>
                <input type='text' class='next-input' v-model='product.name'>
            </div>
            <div class='form-group mb15 row'>
                <div class='col-6'>
                    <label class='text-title-field'>{{ __('order.price') }}</label>
                    <input type='text' class='next-input' v-model='product.price'>
                </div>
                <div class='col-6'>
                    <label class='text-title-field'>{{ __('order.sku_optional') }}</label>
                    <input type='text' class='next-input' v-model='product.sku'>
                </div>
            </div>
            <div class='form-group mb-3'>
                <label class='next-label'>
                    <input type='checkbox' class='hrv-checkbox' v-model='product.with_storehouse_management' value='1'>
                    {{ __('order.with_storehouse_management') }}</label>
            </div>
            <div class='row' v-show='product.with_storehouse_management'>
                <div class='col-8'>
                    <div class='form-group mb-3'>
                        <label class='text-title-field'>{{ __('order.quantity') }}</label>
                        <input type='number' min='1' class='next-input' v-model='product.quantity'>
                    </div>
                    <div class='form-group mb-3'>
                        <label class='next-label'>
                            <input type='checkbox' class='hrv-checkbox'
                                   v-model='product.allow_checkout_when_out_of_stock' value='1'>
                            {{ __('order.allow_customer_checkout_when_this_product_out_of_stock') }}</label>
                    </div>
                </div>
            </div>
            <div class='form-group mb-3' v-if='store && store.id'>
                <label class='next-label'>{{ __('order.store') }}: <strong class='text-primary'>{{
                        store.name
                    }}</strong></label>
            </div>
        </ec-modal>
    </div>
</template>

<script>
export default {
    props: {
        store: {
            type: Object,
            default: () => ({}),
        },
    },
    data: function() {
        return {
            product: {},
        }
    },
    methods: {
        resetProductData: function() {
            this.product = {
                name: null,
                price: 0,
                sku: null,
                with_storehouse_management: false,
                allow_checkout_when_out_of_stock: false,
                quantity: 0,
                tax_price: 0,
            }
        },
    },
    mounted: function() {
        this.resetProductData()
    },
}
</script>
