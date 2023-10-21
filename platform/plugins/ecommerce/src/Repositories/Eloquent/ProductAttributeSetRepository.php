<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Repositories\Interfaces\ProductAttributeSetInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class ProductAttributeSetRepository extends RepositoriesAbstract implements ProductAttributeSetInterface
{
    public function getByProductId(int|array|string|null $productId): Collection
    {
        return ProductAttributeSet::getByProductId($productId);
    }

    public function getAllWithSelected(int|array|string|null $productId, array $with = []): Collection
    {
        return ProductAttributeSet::getAllWithSelected($productId, $with);
    }
}
