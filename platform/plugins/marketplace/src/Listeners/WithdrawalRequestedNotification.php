<?php

namespace Botble\Marketplace\Listeners;

use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Marketplace\Events\WithdrawalRequested;

class WithdrawalRequestedNotification
{
    public function handle(WithdrawalRequested $event): void
    {
        event(new AdminNotificationEvent(
            AdminNotificationItem::make()
                ->title(trans('plugins/marketplace::withdrawal.new_withdrawal_request_notifications.new_withdrawal_request'))
                ->description(trans('plugins/marketplace::withdrawal.new_withdrawal_request_notifications.description', [
                    'customer' => $event->customer->name,
                ]))
                ->action(trans('plugins/marketplace::withdrawal.new_withdrawal_request_notifications.view'), route('marketplace.withdrawal.edit', $event->withdrawal->id))
                ->permission('marketplace.withdrawal.edit')
        ));
    }
}
