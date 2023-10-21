<?php

use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Media\Facades\RvMedia;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $orderProducts = OrderProduct::get();

        $orderProducts->load([
            'product',
            'product.variationInfo',
            'product.variationInfo.configurableProduct',
            'product.taxes',
            'product.variationProductAttributes',
        ]);

        foreach ($orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            $originalProduct = $product->originalProduct;

            $orderOptions = [
                'image' => RvMedia::getImageUrl($product ? $product->image : ($originalProduct ? $originalProduct->image : ''), 'thumb', false, RvMedia::getDefaultImage()),
                'attributes' => $product && $product->is_variation ? $product->variation_attributes : '',
                'taxRate' => $originalProduct ? $originalProduct->total_taxes_percentage : 0,
                'options' => $orderProduct->product_options,
                'extras' => $orderProduct->options,
                'sku' => $product ? $product->sku : '',
                'weight' => $product->weight,
            ];

            $orderProduct->options = $orderOptions;
            $orderProduct->timestamps = false;
            $orderProduct->save();
        }

        if (Schema::hasTable('ec_order_return_items')) {
            Schema::table('ec_order_return_items', function (Blueprint $table) {
                $table->decimal('refund_amount', 12)->default(0)->nullable();
            });

            $orderReturns = OrderReturn::get();

            $orderReturns->load(['order', 'items', 'order.products', 'items.orderProduct']);
            foreach ($orderReturns as $orderReturn) {
                $order = $orderReturn->order;
                $totalRefundAmount = $order->amount - $order->shipping_amount;
                $totalPriceProducts = $order->products->sum(function ($item) {
                    return $item->total_price_with_tax;
                });
                $ratio = $totalRefundAmount <= 0 ? 0 : $totalPriceProducts / $totalRefundAmount;

                foreach ($orderReturn->items as $item) {
                    $orderProduct = $item->orderProduct;
                    $item->refund_amount = $ratio == 0 ? 0 : ($orderProduct->price_with_tax * $item->qty / $ratio);
                    $item->save();
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_order_return_items')) {
            Schema::table('ec_order_return_items', function (Blueprint $table) {
                if (Schema::hasColumn('ec_order_return_items', 'refund_amount')) {
                    $table->dropColumn('refund_amount');
                }
            });
        }

        $orderProducts = OrderProduct::query()->where('options', '<>', '[]')->get();

        foreach ($orderProducts as $orderProduct) {
            $orderProduct->options = Arr::get($orderProduct->options, 'extras', []);
            $orderProduct->timestamps = false;
            $orderProduct->save();
        }
    }
};
