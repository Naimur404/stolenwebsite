<?php

use Botble\Ecommerce\Models\InvoiceItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('ec_invoice_items', function (Blueprint $table) {
            $table->decimal('price', 15)->after('qty')->default(0);
        });

        foreach (InvoiceItem::query()->get() as $invoice) {
            $invoice->price = $invoice->sub_total;
            $invoice->sub_total = $invoice->price * $invoice->qty;
            $invoice->save();

        }
    }
};
