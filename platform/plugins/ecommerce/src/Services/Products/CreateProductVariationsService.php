<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductVariation;

class CreateProductVariationsService
{
    public function execute(Product $product): array
    {
        $attributeSets = $product->productAttributeSets()->allRelatedIds()->toArray();

        $attributes = ProductAttribute::query()
            ->whereIn('attribute_set_id', $attributeSets)
            ->get();

        $data = [];

        foreach ($attributeSets as $attributeSet) {
            $data[] = $attributes
                ->where('attribute_set_id', $attributeSet)
                ->pluck('id')
                ->toArray();
        }

        $variationsInfo = $this->combinations($data);

        $variations = [];
        foreach ($variationsInfo as $value) {
            $result = ProductVariation::getVariationByAttributesOrCreate($product->getKey(), $value);
            $variations[] = $result['variation'];
        }

        return $variations;
    }

    protected function combinations(array $array): array
    {
        $result = [[]];

        foreach ($array as $key => $value) {
            $tmp = [];
            foreach ($result as $item) {
                foreach ($value as $valueItem) {
                    $tmp[] = array_merge($item, [$key => $valueItem]);
                }
            }
            $result = $tmp;
        }

        return $result;
    }
}
