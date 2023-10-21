<template>
    <span>
        <span v-if='item.is_out_of_stock' class='text-danger'>
            <small>&nbsp;({{ __('order.out_of_stock') }})</small>
        </span>
        <span v-else>
            <span v-if='item.with_storehouse_management'>
                <span v-if='item.quantity > 0'>
                    <small>&nbsp;({{ item.quantity }} {{ __('order.products_available') }})</small>
                </span>
                <span v-else class='text-warning'>
                    <small>&nbsp;({{ item.quantity }} {{ __('order.products_available') }})</small>
                </span>
            </span>
        </span>
        <span class='text-info ps-1'>({{ item.formatted_price }})</span>
    </span>
</template>

<script>
export default {
    props: {
        item: {
            type: Object,
            default: () => {
            },
            required: true,
        },
    },
}
</script>
