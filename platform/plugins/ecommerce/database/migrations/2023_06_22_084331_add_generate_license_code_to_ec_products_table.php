<?php

use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ec_products', 'generate_license_code')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->boolean('generate_license_code')->default(false);
            });
        }

        if (! Schema::hasColumn('ec_order_product', 'license_code')) {
            Schema::table('ec_order_product', function (Blueprint $table) {
                $table->uuid('license_code')->nullable();
            });
        }

        $invoices = Invoice::query()
            ->where('reference_type', Order::class)
            ->with([
                'reference',
                'reference.products',
                'items',
            ])
            ->get();

        foreach ($invoices as $invoice) {
            $order = $invoice->reference;
            if ($order && $order->getKey()) {
                $invoiceItems = $invoice->items->whereIn('reference_id', $order->products->pluck('product_id')->all());
                foreach ($invoiceItems as $invoiceItem) {
                    $orderProduct = $order->products->firstWhere('product_id', $invoiceItem->reference_id);
                    if ($orderProduct && $orderProduct->product_options_implode) {
                        $invoiceItem->options = array_merge((array)$invoiceItem->options, [
                            'product_options' => $orderProduct->product_options_implode,
                        ]);

                        $invoiceItem->save();
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_products', 'generate_license_code')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->dropColumn('generate_license_code');
            });
        }

        if (Schema::hasColumn('ec_order_product', 'license_code')) {
            Schema::table('ec_order_product', function (Blueprint $table) {
                $table->dropColumn('license_code');
            });
        }
    }
};
