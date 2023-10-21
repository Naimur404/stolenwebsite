<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductVariation;

class StoreAttributesOfProductService
{
    public function execute(Product $product, array $attributeSets, array $attributes = []): Product
    {
        $product->productAttributeSets()->sync($attributeSets);

        if (! $attributes) {
            $attributes = ProductAttribute::query()
                ->whereIn('attribute_set_id', $attributeSets)
                ->pluck('id')
                ->all();

            $attributes = $this->getSelectedAttributes($product, $attributes);
        }

        ProductVariation::correctVariationItems($product->getKey(), $attributes);

        return $product;
    }

    protected function getSelectedAttributes(Product $product, array $attributes): array
    {
        $attributeSets = $product->productAttributeSets()
            ->select('attribute_set_id')
            ->pluck('attribute_set_id')
            ->toArray();

        $allRelatedAttributeBySet = ProductAttribute::query()
            ->whereIn('attribute_set_id', $attributeSets)
            ->pluck('id')
            ->all();

        $newAttributes = [];

        foreach ($attributes as $item) {
            if (in_array($item, $allRelatedAttributeBySet)) {
                $newAttributes[] = $item;
            }
        }

        return $newAttributes;
    }
}
