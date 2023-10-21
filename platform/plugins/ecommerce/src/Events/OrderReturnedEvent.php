<?php

namespace Botble\Ecommerce\Events;

use Botble\Base\Events\Event;
use Botble\Ecommerce\Models\OrderReturn;
use Illuminate\Queue\SerializesModels;

class OrderReturnedEvent extends Event
{
    use SerializesModels;

    public function __construct(public OrderReturn $order)
    {
    }
}
