<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\ShippingStatusChanged;
use Botble\Ecommerce\Facades\OrderHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendShippingStatusChangedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ShippingStatusChanged $event): void
    {
        if ($event->shipment->status == ShippingStatusEnum::DELIVERING) {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('customer_delivery_order')) {
                $order = $event->shipment->order;

                OrderHelper::setEmailVariables($order);
                $mailer->sendUsingTemplate(
                    'customer_delivery_order',
                    $order->user->email ?: $order->address->email
                );
            }
        }

        if ($event->shipment->status == ShippingStatusEnum::DELIVERED) {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('customer_order_delivered')) {
                $order = $event->shipment->order;

                OrderHelper::setEmailVariables($order);
                $mailer->sendUsingTemplate(
                    'customer_order_delivered',
                    $order->user->email ?: $order->address->email
                );
            }

            event(new AdminNotificationEvent(
                AdminNotificationItem::make()
                    ->title(trans('plugins/ecommerce::order.order_completed_notifications.order_completed'))
                    ->description(trans('plugins/ecommerce::order.order_completed_notifications.description', [
                        'order' => $event->shipment->order->code,
                    ]))
                    ->action(trans('plugins/ecommerce::order.new_order_notifications.view'), route('orders.edit', $event->shipment->order->id))
            ));
        } else {
            event(new AdminNotificationEvent(
                AdminNotificationItem::make()
                    ->title(trans('plugins/ecommerce::order.update_shipping_status_notifications.update_shipping_status'))
                    ->description(trans('plugins/ecommerce::order.update_shipping_status_notifications.description', [
                        'order' => $event->shipment->order->code,
                        'description' => $event->previousShipment ? ' from ' . ShippingStatusEnum::getLabel($event->previousShipment['status']) . ' to ' .
                            ShippingStatusEnum::getLabel($event->shipment->status) : ' to ' . ShippingStatusEnum::getLabel($event->shipment->status),
                    ]))
                    ->action(trans('plugins/ecommerce::order.new_order_notifications.view'), route('orders.edit', $event->shipment->order->id))
            ));
        }
    }
}
