<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Models\GroupedProduct;
use Botble\Ecommerce\Repositories\Interfaces\GroupedProductInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class GroupedProductRepository extends RepositoriesAbstract implements GroupedProductInterface
{
    public function getChildren($groupedProductId, array $params)
    {
        return GroupedProduct::getChildren($groupedProductId);
    }

    public function createGroupedProducts($groupedProductId, array $childItems)
    {
        return GroupedProduct::createGroupedProducts($groupedProductId, $childItems);
    }
}
