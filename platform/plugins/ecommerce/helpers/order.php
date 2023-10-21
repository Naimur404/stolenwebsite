<?php

if (! function_exists('render_cart_form')) {
    function render_cart_form(): string
    {
        return view('plugins/ecommerce::orders.partials.cart')->render();
    }
}

if (! function_exists('get_order_code')) {
    function get_order_code(int|string $orderId): string
    {
        $prefix = get_ecommerce_setting('store_order_prefix') ? get_ecommerce_setting('store_order_prefix') . '-' : '';
        $suffix = get_ecommerce_setting('store_order_suffix') ? '-' . get_ecommerce_setting('store_order_suffix') : '';

        return '#' . $prefix . ((int)config('plugins.ecommerce.order.default_order_start_number') + $orderId) . $suffix;
    }
}

if (! function_exists('get_order_id_from_order_code')) {
    function get_order_id_from_order_code(string $code): int
    {
        $prefix = '#' . (get_ecommerce_setting('store_order_prefix') ? (get_ecommerce_setting('store_order_prefix') . '-') : '');
        $suffix = get_ecommerce_setting('store_order_suffix') ? '-' . get_ecommerce_setting('store_order_suffix') : '';

        $orderId = substr($code, strlen($prefix));

        if ($suffix) {
            $orderId = substr($orderId, 0, strrpos($orderId, $suffix));
        }

        return (int)$orderId - (int)config('plugins.ecommerce.order.default_order_start_number');
    }
}
