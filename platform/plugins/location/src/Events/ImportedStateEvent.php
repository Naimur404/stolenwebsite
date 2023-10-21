<?php

namespace Botble\Location\Events;

use Botble\Base\Events\Event;
use Botble\Location\Models\State;
use Illuminate\Queue\SerializesModels;

class ImportedStateEvent extends Event
{
    use SerializesModels;

    public function __construct(public array $row, public State $state)
    {
    }
}
