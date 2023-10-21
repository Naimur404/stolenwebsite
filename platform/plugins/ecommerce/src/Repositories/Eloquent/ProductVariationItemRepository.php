<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Models\ProductVariationItem;
use Botble\Ecommerce\Repositories\Interfaces\ProductVariationItemInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class ProductVariationItemRepository extends RepositoriesAbstract implements ProductVariationItemInterface
{
    public function getVariationsInfo(array $versionIds)
    {
        return ProductVariationItem::getVariationsInfo($versionIds);
    }

    public function getProductAttributes($productId)
    {
        return ProductVariationItem::getProductAttributes($productId);
    }
}
