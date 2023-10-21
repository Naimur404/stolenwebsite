<?php

use Botble\Ecommerce\Models\Invoice;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        try {
            foreach (Invoice::with('reference')->get() as $invoice) {
                $invoice->customer_address = $invoice->reference->shippingAddress->full_address;
                $invoice->save();
            }
        } catch (Throwable $exception) {
            info($exception->getMessage());
        }
    }
};
