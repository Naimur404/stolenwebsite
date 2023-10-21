<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Base\Facades\MetaBox;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Str;

class DuplicateProductService
{
    public function handle(Product $model): Product
    {
        $product = $model->replicate();

        if ($product->sku) {
            $product->sku = $product->sku . '-' . Str::random(5);
        }

        $product->views = 0;

        $product->save();

        if ($model->faq_items) {
            MetaBox::saveMetaBoxData($product, 'faq_schema_config', $model->faq_items);
        }

        if ($categories = $model->categories()->pluck('category_id')->all()) {
            $product->categories()->sync($categories);
        }

        if ($productAttributeSets = $model->productAttributeSets()->pluck('attribute_set_id')->all()) {
            $product->productAttributeSets()->sync($productAttributeSets);
        }

        if ($productCollections = $model->productCollections()->pluck('product_collection_id')->all()) {
            $product->productCollections()->sync($productCollections);
        }

        if ($productLabels = $model->productLabels()->pluck('product_label_id')->all()) {
            $product->productLabels()->sync($productLabels);
        }

        if ($crossSales = $model->crossSales()->pluck('to_product_id')->all()) {
            $product->crossSales()->sync($crossSales);
        }

        if ($upSales = $model->upSales()->pluck('to_product_id')->all()) {
            $product->upSales()->sync($upSales);
        }
        if ($groupedProducts = $model->groupedProduct()->pluck('product_id')->all()) {
            $product->groupedProduct()->sync($groupedProducts);
        }

        if ($products = $model->products()->pluck('to_product_id')->all()) {
            $product->products()->sync($products);
        }

        if ($taxes = $model->taxes()->pluck('tax_id')->all()) {
            $product->taxes()->sync($taxes);
        }

        if ($tags = $model->tags()->pluck('tag_id')->all()) {
            $product->tags()->sync($tags);
        }

        if ($groupedItems = $model->groupedItems()->get()->toArray()) {
            $product->groupedItems()->createMany($groupedItems);
        }

        if ($options = $model->options()->with('values')->get()) {
            $productOptions = $product->options()->createMany($options->toArray());

            foreach ($options as $option) {
                if ($option->values->isEmpty()) {
                    continue;
                }

                /** @var Option $productOptions */
                $productOptions
                    ->where('name', $option->name)
                    ->first()
                    ->values()
                    ->createMany($option->values->toArray());
            }
        }

        if ($variations = $model->variations()->with('product')->get()) {
            foreach ($variations as $variation) {
                $productVariation = $variation->product->replicate();

                if ($productVariation->sku) {
                    $productVariation->sku = $productVariation->sku . '-' . Str::random(5);
                }

                $productVariation->views = 0;

                $productVariation->save();

                /**
                 * @var ProductVariation $productVariationRelation
                 */
                $productVariationRelation = $product->variations()->create([
                    'product_id' => $productVariation->getKey(),
                    'configurable_product_id' => $product->getKey(),
                    'is_default' => $variation->is_default,
                ]);

                $productVariationRelation->productAttributes()->attach($variation->productAttributes()->pluck('attribute_id')->all());
            }
        }

        if (
            EcommerceHelper::isEnabledSupportDigitalProducts() &&
            $model->isTypeDigital() &&
            $productFiles = $model->productFiles()->get()->toArray()
        ) {
            $product->productFiles()->createMany($productFiles);
        }

        Slug::query()->create([
            'reference_type' => Product::class,
            'reference_id' => $product->getKey(),
            'key' => Str::slug($product->name),
            'prefix' => SlugHelper::getPrefix(Product::class),
        ]);

        return $product;
    }
}
