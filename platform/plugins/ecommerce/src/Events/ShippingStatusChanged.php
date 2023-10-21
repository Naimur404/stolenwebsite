<?php

namespace Botble\Ecommerce\Events;

use Botble\Base\Events\Event;
use Botble\Ecommerce\Models\Shipment;
use Illuminate\Queue\SerializesModels;

class ShippingStatusChanged extends Event
{
    use SerializesModels;

    public function __construct(public Shipment $shipment, public array $previousShipment = [])
    {
    }
}
