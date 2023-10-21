<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface FlashSaleInterface extends RepositoryInterface
{
    public function getAvailableFlashSales(array $with = []): Collection;
}
