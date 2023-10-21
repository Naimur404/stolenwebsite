<?php

namespace Botble\Payment\Events;

use Botble\Base\Events\Event;

class RenderedPaymentMethods extends Event
{
    public function __construct(public string $html)
    {
    }
}
