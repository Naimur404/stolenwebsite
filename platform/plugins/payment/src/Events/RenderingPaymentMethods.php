<?php

namespace Botble\Payment\Events;

use Botble\Base\Events\Event;

class RenderingPaymentMethods extends Event
{
    public function __construct(public array $methods)
    {
    }
}
