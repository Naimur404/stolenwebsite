<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;

class UpdateProductStockStatus
{
    public function handle(ProductQuantityUpdatedEvent $event): void
    {
        $product = $event->product;

        if (! $product->is_variation) {
            return;
        }

        $parentProduct = $product->original_product;

        if (! $parentProduct || ! $parentProduct->id || $parentProduct->is_variation) {
            return;
        }

        $variations = $parentProduct->variations()->with('product')->get();

        $quantity = 0;
        $withStorehouseManagement = false;
        $stockStatus = StockStatusEnum::OUT_OF_STOCK;
        $allowCheckoutWhenOutOfStock = false;

        foreach ($variations as $variation) {
            $product = $variation->product;

            if (! $product || ! $product->is_variation) {
                continue;
            }

            if ($product->with_storehouse_management) {
                $quantity += $product->quantity;
                $withStorehouseManagement = true;
            }

            if ($product->allow_checkout_when_out_of_stock) {
                $allowCheckoutWhenOutOfStock = true;
            }

            if (! $product->isOutOfStock()) {
                $stockStatus = StockStatusEnum::IN_STOCK;
            }
        }

        $parentProduct->quantity = $quantity;
        $parentProduct->with_storehouse_management = $withStorehouseManagement;
        $parentProduct->stock_status = $stockStatus;
        $parentProduct->allow_checkout_when_out_of_stock = $allowCheckoutWhenOutOfStock;

        $parentProduct->save();
    }
}
