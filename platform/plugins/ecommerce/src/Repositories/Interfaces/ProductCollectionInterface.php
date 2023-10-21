<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ProductCollectionInterface extends RepositoryInterface
{
    public function createSlug(string $name, int|string|null $id): string;
}
