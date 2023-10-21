<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface DiscountInterface extends RepositoryInterface
{
    public function getAvailablePromotions(array $with = [], bool $forProductSingle = false);

    public function getProductPriceBasedOnPromotion(array $productIds = [], array $productCollectionIds = []);
}
