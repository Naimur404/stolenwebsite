<?php

namespace Botble\Location\Events;

use Botble\Base\Events\Event;
use Botble\Location\Models\Country;
use Illuminate\Queue\SerializesModels;

class ImportedCountryEvent extends Event
{
    use SerializesModels;

    public function __construct(public array $row, public Country $country)
    {
    }
}
