<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;

class OrderPaymentConfirmedNotification
{
    public function handle(OrderPaymentConfirmedEvent $event): void
    {
        event(new AdminNotificationEvent(
            AdminNotificationItem::make()
                ->title(trans('plugins/ecommerce::order.confirm_payment_notifications.confirm_payment'))
                ->description(trans('plugins/ecommerce::order.confirm_payment_notifications.description', [
                    'order' => $event->order->code,
                    'amount' => format_price($event->order->amount),
                    'by' => $event->confirmedBy->username,
                ]))
                ->action(trans('plugins/ecommerce::order.new_order_notifications.view'), route('orders.edit', $event->order->id))
        ));
    }
}
