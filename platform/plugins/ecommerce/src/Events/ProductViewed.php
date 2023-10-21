<?php

namespace Botble\Ecommerce\Events;

use Botble\Base\Events\Event;
use Botble\Ecommerce\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Queue\SerializesModels;

class ProductViewed extends Event
{
    use SerializesModels;

    public function __construct(public Product $product, public CarbonInterface $dateTime)
    {
    }
}
