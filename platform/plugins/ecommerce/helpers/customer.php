<?php

use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Wishlist;
use Illuminate\Support\Collection;

if (! function_exists('is_added_to_wishlist')) {
    function is_added_to_wishlist(int|string $productId): bool
    {
        if (! auth('customer')->check()) {
            return false;
        }

        return Wishlist::query()
            ->where([
                'product_id' => $productId,
                'customer_id' => auth('customer')->id(),
            ])
            ->exists();
    }
}

if (! function_exists('count_customer_addresses')) {
    function count_customer_addresses(): int
    {
        if (! auth('customer')->check()) {
            return 0;
        }

        return Address::query()->where('customer_id', auth('customer')->id())->count();
    }
}

if (! function_exists('get_customer_addresses')) {
    function get_customer_addresses(): Collection
    {
        if (! auth('customer')->check()) {
            return collect();
        }

        return Address::query()
            ->where('customer_id', auth('customer')->id())
            ->orderByDesc('created_at')
            ->get();
    }
}

if (! function_exists('get_default_customer_address')) {
    function get_default_customer_address(): ?Address
    {
        if (! auth('customer')->check()) {
            return null;
        }

        return Address::query()
            ->where([
                'is_default' => 1,
                'customer_id' => auth('customer')->id(),
            ])
            ->first();
    }
}
