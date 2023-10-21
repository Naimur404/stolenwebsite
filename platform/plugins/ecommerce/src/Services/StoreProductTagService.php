<?php

namespace Botble\Ecommerce\Services;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductTag;
use Illuminate\Http\Request;

class StoreProductTagService
{
    public function execute(Request $request, Product $product): void
    {
        $tags = $product->tags->pluck('name')->all();

        $tagsInput = collect(json_decode((string)$request->input('tag'), true))->pluck('value')->all();

        if (count($tags) != count($tagsInput) || count(array_diff($tags, $tagsInput)) > 0) {
            $product->tags()->detach();

            $tagIds = [];

            foreach ($tagsInput as $tagName) {
                if (! trim($tagName)) {
                    continue;
                }

                $tag = ProductTag::query()->where('name', $tagName)->first();

                if ($tag === null && ! empty($tagName)) {
                    $tag = ProductTag::query()->create(['name' => $tagName]);

                    $request->merge(['slug' => $tagName]);

                    event(new CreatedContentEvent(PRODUCT_TAG_MODULE_SCREEN_NAME, $request, $tag));
                }

                if (! empty($tag)) {
                    $tagIds[] = $tag->getKey();
                }
            }

            $product->tags()->sync(array_unique($tagIds));
        }
    }
}
