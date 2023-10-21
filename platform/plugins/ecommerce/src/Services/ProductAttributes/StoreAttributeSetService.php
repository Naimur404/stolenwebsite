<?php

namespace Botble\Ecommerce\Services\ProductAttributes;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Illuminate\Http\Request;

class StoreAttributeSetService
{
    public function execute(Request $request, ProductAttributeSet $productAttributeSet): ProductAttributeSet
    {
        $existing = $productAttributeSet->exists;

        $data = $request->input();

        $productAttributeSet->fill($data);
        $productAttributeSet->save();

        if (! $existing) {
            event(new CreatedContentEvent(PRODUCT_ATTRIBUTE_SETS_MODULE_SCREEN_NAME, $request, $productAttributeSet));
        } else {
            event(new UpdatedContentEvent(PRODUCT_ATTRIBUTE_SETS_MODULE_SCREEN_NAME, $request, $productAttributeSet));
        }

        $attributes = json_decode($request->input('attributes', '[]'), true) ?: [];
        $deletedAttributes = json_decode($request->input('deleted_attributes', '[]'), true) ?: [];

        $this->deleteAttributes($productAttributeSet->getKey(), $deletedAttributes);
        $this->storeAttributes($productAttributeSet->getKey(), $attributes);

        return $productAttributeSet;
    }

    protected function deleteAttributes(int|string $productAttributeSetId, array $attributeIds): void
    {
        foreach ($attributeIds as $id) {
            $attribute = ProductAttribute::query()->where([
                'id' => $id,
                'attribute_set_id' => $productAttributeSetId,
            ])->first();

            if ($attribute) {
                $attribute->delete();
                event(new DeletedContentEvent(PRODUCT_ATTRIBUTES_MODULE_SCREEN_NAME, request(), $attribute));
            }
        }
    }

    protected function storeAttributes(int|string $productAttributeSetId, array $attributes): void
    {
        foreach ($attributes as $item) {
            if (isset($item['id'])) {
                $attribute = ProductAttribute::query()->find($item['id']);
                if (! $attribute) {
                    $item['attribute_set_id'] = $productAttributeSetId;
                    $attribute = ProductAttribute::query()->create($item);

                    event(new CreatedContentEvent(PRODUCT_ATTRIBUTES_MODULE_SCREEN_NAME, request(), $attribute));
                } else {
                    $attribute->fill($item);
                    $attribute->save();

                    event(new UpdatedContentEvent(PRODUCT_ATTRIBUTES_MODULE_SCREEN_NAME, request(), $attribute));
                }
            }
        }
    }
}
