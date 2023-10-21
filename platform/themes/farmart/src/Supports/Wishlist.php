<?php

namespace Theme\Farmart\Supports;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;

class Wishlist
{
    public static function getWishlistIds(array $productIds = []): array
    {
        if (! EcommerceHelper::isWishlistEnabled()) {
            return [];
        }

        if (auth('customer')->check()) {
            return auth('customer')->user()->wishlist()->whereIn('product_id', $productIds)->pluck('product_id')->all();
        }

        return collect(Cart::instance('wishlist')->content())
            ->sortByDesc('updated_at')
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->all();
    }
}
