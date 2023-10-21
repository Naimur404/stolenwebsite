<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface GroupedProductInterface extends RepositoryInterface
{
    public function getChildren($groupedProductId, array $params);

    public function createGroupedProducts($groupedProductId, array $childItems);
}
