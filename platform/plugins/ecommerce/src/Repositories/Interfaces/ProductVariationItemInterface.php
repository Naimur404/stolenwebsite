<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ProductVariationItemInterface extends RepositoryInterface
{
    public function getVariationsInfo(array $versionIds);

    public function getProductAttributes($productId);
}
