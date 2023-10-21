<?php

namespace Botble\Ecommerce\Events;

use Botble\ACL\Models\User;
use Botble\Base\Events\Event;
use Botble\Ecommerce\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedEvent extends Event
{
    use SerializesModels;

    public function __construct(public Order $order, public User $confirmedBy)
    {
    }
}
