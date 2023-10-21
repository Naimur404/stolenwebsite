<?php

namespace Botble\Ecommerce\Commands;

use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand('cms:abandoned-carts:email', 'Send emails abandoned carts')]
class SendAbandonedCartsEmailCommand extends Command
{
    public function handle(): int
    {
        $orders = Order::query()
            ->with(['user', 'address'])
            ->where('is_finished', 0)
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $email = $order->user->email ?: $order->address->email;

            if (! $email) {
                continue;
            }

            try {
                $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
                $order->dont_show_order_info_in_product_list = true;
                OrderHelper::setEmailVariables($order);

                $mailer->sendUsingTemplate('order_recover', $email);

                $count++;
            } catch (Throwable $exception) {
                info($exception->getMessage());

                return self::FAILURE;
            }
        }

        $this->info('Send ' . $count . ' email' . ($count != 1 ? 's' : '') . ' successfully!');

        return self::SUCCESS;
    }
}
