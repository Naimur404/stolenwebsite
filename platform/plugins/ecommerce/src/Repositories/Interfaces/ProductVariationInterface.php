<?php

namespace Botble\Ecommerce\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface ProductVariationInterface extends RepositoryInterface
{
    public function getVariationByAttributes($configurableProductId, array $attributes);

    public function getVariationByAttributesOrCreate($configurableProductId, array $attributes);

    public function correctVariationItems($configurableProductId, array $attributes);

    public function getParentOfVariation($variationId, array $with = []);

    public function getAttributeIdsOfChildrenProduct($productId);
}
