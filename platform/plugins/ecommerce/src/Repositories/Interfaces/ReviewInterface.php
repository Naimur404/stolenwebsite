<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface ReviewInterface extends RepositoryInterface
{
    public function getGroupedByProductId(int|string $productId): Collection;
}
