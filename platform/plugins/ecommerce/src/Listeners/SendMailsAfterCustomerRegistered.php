<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Auth\Events\Registered;

class SendMailsAfterCustomerRegistered
{
    public function handle(Registered $event): void
    {
        $customer = $event->user;

        if (get_class($customer) == Customer::class) {
            EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                ])
                ->sendUsingTemplate('welcome', $customer->email);

            if (EcommerceHelper::isEnableEmailVerification()) {
                $customer->sendEmailVerificationNotification();
            }
        }
    }
}
