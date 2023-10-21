<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Str;

class GenerateLicenseCodeAfterOrderCompleted
{
    public function handle(OrderCompletedEvent $event): void
    {
        if (($order = $event->order) instanceof Order && $order->loadMissing(['products.product'])) {
            $orderProducts = $order->products
                ->where(function ($item) {
                    return $item->product->isTypeDigital() && $item->product->generate_license_code;
                });

            $invoiceItems = $order->invoice->items;
            foreach ($orderProducts as $orderProduct) {
                $licenseCode = Str::uuid();
                $orderProduct->license_code = $licenseCode;
                $orderProduct->save();

                $invoiceItem = $invoiceItems->where('reference_id', $orderProduct->product_id)->where('reference_type', Product::class)->first();
                if ($invoiceItem) {
                    $invoiceItem->options = array_merge($invoiceItem->options, [
                        'license_code' => $licenseCode,
                    ]);
                    $invoiceItem->save();
                }
            }
        }
    }
}
