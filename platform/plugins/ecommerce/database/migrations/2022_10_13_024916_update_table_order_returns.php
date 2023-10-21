<?php

use Botble\Ecommerce\Models\OrderReturn;
use Botble\Ecommerce\Models\OrderReturnItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_order_returns', 'code')) {
            Schema::table('ec_order_returns', function (Blueprint $table) {
                $table->string('code')->after('id')->unique()->nullable();
            });
        }

        foreach (OrderReturn::get() as $orderReturn) {
            $orderReturn->code = get_order_code($orderReturn->id);
            $orderReturn->save();
        }

        if (! Schema::hasColumn('ec_order_return_items', 'product_image')) {
            Schema::table('ec_order_return_items', function (Blueprint $table) {
                $table->string('product_image')->after('product_name')->nullable();
            });
        }

        foreach (OrderReturnItem::with('product')->get() as $orderReturnItem) {
            $orderReturnItem->product_image = $orderReturnItem->product->image ?: $orderReturnItem->product->original_product->image;
            $orderReturnItem->save();
        }
    }
};
