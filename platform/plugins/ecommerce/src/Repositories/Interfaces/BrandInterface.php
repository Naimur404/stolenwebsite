<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface BrandInterface extends RepositoryInterface
{
    public function getAll(array $condition = []): Collection;
}
