<?php

if (! function_exists('get_shipping_setting')) {
    function get_shipping_setting(string $key, string|null $type = null, $default = null): array|string|null
    {
        $key = config('plugins.ecommerce.shipping.settings.prefix') . ($type ? $type . '_' : '') . $key;

        return setting($key, $default ?: config('plugins.ecommerce.shipping.' . $key));
    }
}
