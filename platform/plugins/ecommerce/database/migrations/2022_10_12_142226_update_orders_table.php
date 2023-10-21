<?php

use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_orders', 'code')) {
            Schema::table('ec_orders', function (Blueprint $table) {
                $table->string('code')->after('id')->unique()->nullable();
            });
        }

        foreach (Order::get() as $order) {
            $order->code = get_order_code($order->id);
            $order->save();
        }

        if (! Schema::hasColumn('ec_order_product', 'product_image')) {
            Schema::table('ec_order_product', function (Blueprint $table) {
                $table->string('product_image')->after('product_name')->nullable();
            });
        }

        foreach (OrderProduct::with('product')->get() as $orderProduct) {
            $orderProduct->product_image = $orderProduct->product->image ?: $orderProduct->product->original_product->image;
            $orderProduct->save();
        }
    }

    public function down(): void
    {
        Schema::table('ec_orders', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('ec_order_product', function (Blueprint $table) {
            $table->dropColumn('product_image');
        });
    }
};
