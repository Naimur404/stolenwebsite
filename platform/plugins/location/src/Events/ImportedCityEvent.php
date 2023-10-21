<?php

namespace Botble\Location\Events;

use Botble\Base\Events\Event;
use Botble\Location\Models\City;
use Illuminate\Queue\SerializesModels;

class ImportedCityEvent extends Event
{
    use SerializesModels;

    public function __construct(public array $row, public City $city)
    {
    }
}
