<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Ecommerce\Events\OrderReturnedEvent;

class OrderReturnedNotification
{
    public function handle(OrderReturnedEvent $event): void
    {
        event(new AdminNotificationEvent(
            AdminNotificationItem::make()
                ->title(trans('plugins/ecommerce::order.return_order_notifications.return_order'))
                ->description(trans('plugins/ecommerce::order.return_order_notifications.description', [
                    'customer' => auth('customer')->user()->name,
                ]))
                ->action(trans('plugins/ecommerce::order.new_order_notifications.view'), route('order_returns.edit', $event->order->id))
        ));
    }
}
