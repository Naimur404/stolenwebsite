<?php

namespace Botble\Location\Events;

use Botble\Base\Events\Event;
use Illuminate\Queue\SerializesModels;

class DownloadedStates extends Event
{
    use SerializesModels;
}
