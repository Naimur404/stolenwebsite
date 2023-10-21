<?php

namespace Botble\Ecommerce\Traits;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\AddAttributesToProductRequest;
use Botble\Ecommerce\Http\Requests\CreateProductWhenCreatingOrderRequest;
use Botble\Ecommerce\Http\Requests\ProductUpdateOrderByRequest;
use Botble\Ecommerce\Http\Requests\ProductVersionRequest;
use Botble\Ecommerce\Http\Requests\SearchProductAndVariationsRequest;
use Botble\Ecommerce\Http\Resources\AvailableProductResource;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\ProductVariationItem;
use Botble\Ecommerce\Services\Products\CreateProductVariationsService;
use Botble\Ecommerce\Services\Products\StoreAttributesOfProductService;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Media\Facades\RvMedia;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait ProductActionsTrait
{
    public function postSaveAllVersions(
        array $versionInRequest,
        int|string $id,
        BaseHttpResponse $response,
        bool $isUpdateProduct = true
    ) {
        $product = Product::query()->findOrFail($id);

        foreach ($versionInRequest as $variationId => $version) {
            $variation = ProductVariation::query()->find($variationId);

            if (! $variation) {
                continue;
            }

            if (! $variation->product_id || $isUpdateProduct) {
                $isNew = false;
                $productRelatedToVariation = Product::query()->find($variation->product_id);

                if (! $productRelatedToVariation) {
                    $productRelatedToVariation = new Product();
                    $isNew = true;
                }

                $version['images'] = array_values(array_filter((array)Arr::get($version, 'images', []) ?: []));

                $productRelatedToVariation->fill($version);

                $productRelatedToVariation->name = $product->name;
                $productRelatedToVariation->status = $product->status;
                $productRelatedToVariation->brand_id = $product->brand_id;
                $productRelatedToVariation->is_variation = 1;

                $productRelatedToVariation->sku = Arr::get($version, 'sku');
                if (! $productRelatedToVariation->sku && Arr::get($version, 'auto_generate_sku')) {
                    $productRelatedToVariation->sku = $product->sku;
                    if (isset($version['attribute_sets']) && is_array($version['attribute_sets'])) {
                        foreach ($version['attribute_sets'] as $attributeId) {
                            $attribute = ProductAttribute::query()->find($attributeId);
                            if ($attribute) {
                                $productRelatedToVariation->sku = ($productRelatedToVariation->sku ?: $product->id) . '-' . Str::upper(
                                    $attribute->slug
                                );
                            }
                        }
                    }
                }
                $productRelatedToVariation->price = Arr::get($version, 'price', $product->price);
                $productRelatedToVariation->sale_price = Arr::get($version, 'sale_price', $product->sale_price);
                $productRelatedToVariation->description = Arr::get($version, 'description');

                $productRelatedToVariation->length = Arr::get($version, 'length', $product->length);
                $productRelatedToVariation->wide = Arr::get($version, 'wide', $product->wide);
                $productRelatedToVariation->height = Arr::get($version, 'height', $product->height);
                $productRelatedToVariation->weight = Arr::get($version, 'weight', $product->weight);

                $productRelatedToVariation->sale_type = (int)Arr::get($version, 'sale_type', $product->sale_type);

                if ($productRelatedToVariation->sale_type == 0) {
                    $productRelatedToVariation->start_date = null;
                    $productRelatedToVariation->end_date = null;
                } else {
                    $productRelatedToVariation->start_date = Arr::get($version, 'start_date', $product->start_date);
                    $productRelatedToVariation->end_date = Arr::get($version, 'end_date', $product->end_date);
                }

                if ($isNew) {
                    $productRelatedToVariation->product_type = Arr::get(
                        $version,
                        'product_type',
                        $product->product_type
                    );
                    $productRelatedToVariation->images = json_encode($version['images']);
                }

                $productRelatedToVariation->save();

                if (EcommerceHelper::isEnabledSupportDigitalProducts()) {
                    if ($isNew && $product->productFiles->count()) {
                        foreach ($product->productFiles as $productFile) {
                            $productRelatedToVariation->productFiles()->create($productFile->toArray());
                        }
                    } else {
                        app(StoreProductService::class)->saveProductFiles(request(), $productRelatedToVariation);
                    }
                }

                if (! $productRelatedToVariation->is_variation) {
                    if ($isNew) {
                        event(
                            new CreatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, request(), $productRelatedToVariation)
                        );
                    } else {
                        event(
                            new UpdatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, request(), $productRelatedToVariation)
                        );
                    }
                }

                event(new ProductQuantityUpdatedEvent($variation->product));

                $variation->product_id = $productRelatedToVariation->id;
            }

            $variation->is_default = Arr::get($version, 'variation_default_id', 0) == $variation->id;

            $variation->save();

            if (isset($version['attribute_sets']) && is_array($version['attribute_sets'])) {
                $variation->productAttributes()->sync($version['attribute_sets']);
            }
        }

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function postAddAttributeToProduct(
        int|string $id,
        AddAttributesToProductRequest $request,
        BaseHttpResponse $response,
        StoreAttributesOfProductService $storeAttributesOfProductService
    ) {
        $product = Product::query()->findOrFail($id);

        $addedAttributes = array_filter((array)$request->input('added_attributes', []));
        $addedAttributeSets = array_filter((array)$request->input('added_attribute_sets', []));

        if ($addedAttributes && $addedAttributeSets) {
            $result = ProductVariation::getVariationByAttributesOrCreate($id, $addedAttributes);

            $storeAttributesOfProductService->execute($product, $addedAttributeSets, $addedAttributes);

            $variation = $result['variation']->toArray();
            $variation['variation_default_id'] = $variation['id'];
            $variation['auto_generate_sku'] = true;

            $this->postSaveAllVersions([$variation['id'] => $variation], $id, $response);
        }

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $product = Product::query()->findOrFail($id);

        try {
            $product->delete();
            event(new DeletedContentEvent(PRODUCT_MODULE_SCREEN_NAME, $request, $product));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deleteVersion(
        int|string $variationId,
        BaseHttpResponse $response
    ) {
        $variation = ProductVariation::query()->findOrFail($variationId);

        $originProduct = $variation->configurableProduct;
        $result = $this->deleteVersionItem($variationId);

        if ($result) {
            return $response
                ->setData([
                    'total_product_variations' => $originProduct->variations()->count(),
                ])
                ->setMessage(trans('core/base::notices.delete_success_message'));
        }

        return $response
            ->setError()
            ->setMessage(trans('core/base::notices.delete_error_message'));
    }

    public function deleteVersions(
        Request $request,
        BaseHttpResponse $response
    ) {
        $ids = (array)$request->input('ids');

        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        $variations = ProductVariation::query()->whereIn('id', $ids)->get();

        if (! $variations->count() || $variations->count() != count($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $this->deleteVersionItem($id);
        }

        $variation = $variations->first();
        $originProduct = Product::query()->find($variation->configurable_product_id);

        return $response
            ->setData([
                'total_product_variations' => $originProduct->variations()->count(),
            ])
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }

    protected function deleteVersionItem(int|string $variationId)
    {
        $variation = ProductVariation::query()->find($variationId);

        if (! $variation) {
            return true;
        }

        ProductVariationItem::query()->where('variation_id', $variationId)->delete();

        $productRelatedToVariation = Product::query()->find($variation->product_id);

        if ($productRelatedToVariation) {
            $productRelatedToVariation->delete();

            event(new DeletedContentEvent(PRODUCT_MODULE_SCREEN_NAME, request(), $productRelatedToVariation));
        }

        $result = $variation->delete();

        $originProduct = Product::query()->find($variation->configurable_product_id);

        if ($variation->is_default) {
            $latestVariation = ProductVariation::query()
                ->where('configurable_product_id', $variation->configurable_product_id)
                ->first();

            if ($latestVariation) {
                $latestVariation->is_default = 1;

                $latestVariation->save();

                if ($originProduct && $latestVariation->product->id) {
                    $originProduct->sku = $latestVariation->product->sku;
                    $originProduct->price = $latestVariation->product->price;
                    $originProduct->length = $latestVariation->product->length;
                    $originProduct->wide = $latestVariation->product->wide;
                    $originProduct->height = $latestVariation->product->height;
                    $originProduct->weight = $latestVariation->product->weight;
                    $originProduct->sale_price = $latestVariation->product->sale_price;
                    $originProduct->sale_type = $latestVariation->product->sale_type;
                    $originProduct->start_date = $latestVariation->product->start_date;
                    $originProduct->end_date = $latestVariation->product->end_date;
                    $originProduct->save();
                }
            } else {
                $originProduct->productAttributeSets()->detach();
            }
        }

        $originProduct && event(new ProductQuantityUpdatedEvent($originProduct));

        return $result;
    }

    public function postAddVersion(ProductVersionRequest $request, int|string|null $id, BaseHttpResponse $response)
    {
        $addedAttributes = $request->input('attribute_sets', []);

        if (! empty($addedAttributes) && is_array($addedAttributes)) {
            $result = ProductVariation::getVariationByAttributesOrCreate($id, $addedAttributes);
            if (! $result['created']) {
                return $response
                    ->setError()
                    ->setMessage(
                        trans('plugins/ecommerce::products.form.variation_existed') . ' #' . $result['variation']->id
                    );
            }

            $this->postSaveAllVersions(
                [$result['variation']->id => $request->input()],
                $id,
                $response
            );

            return $response->setMessage(trans('plugins/ecommerce::products.form.added_variation_success'));
        }

        return $response
            ->setError()
            ->setMessage(trans('plugins/ecommerce::products.form.no_attributes_selected'));
    }

    public function getVersionForm(int|string|null $id, Request $request, BaseHttpResponse $response)
    {
        $product = null;
        $variation = null;
        $productVariationsInfo = collect();

        if ($id) {
            $variation = ProductVariation::query()->findOrFail($id);
            $product = Product::query()->findOrFail($variation->product_id);
            $productVariationsInfo = ProductVariationItem::getVariationsInfo([$id]);
            $originalProduct = $product;
        } else {
            $originalProduct = Product::query()->findOrFail($request->input('product_id'));
        }

        $productId = $variation ? $variation->configurable_product_id : $request->input('product_id');

        if ($productId) {
            $productAttributeSets = ProductAttributeSet::getByProductId($productId);
        } else {
            $productAttributeSets = ProductAttributeSet::getAllWithSelected($productId);
        }

        $html = view(
            'plugins/ecommerce::products.partials.product-variation-form',
            compact(
                'productAttributeSets',
                'product',
                'productVariationsInfo',
                'originalProduct'
            )
        )->render();

        return $response->setData($html);
    }

    public function postUpdateVersion(ProductVersionRequest $request, int|string $id, BaseHttpResponse $response)
    {
        $variation = ProductVariation::query()->findOrFail($id);

        $addedAttributes = $request->input('attribute_sets', []);

        if (! empty($addedAttributes) && is_array($addedAttributes)) {
            $result = ProductVariation::getVariationByAttributesOrCreate(
                $variation->configurable_product_id,
                $addedAttributes
            );

            if (! $result['created'] && $result['variation']->id !== $variation->getKey()) {
                return $response
                    ->setError()
                    ->setData($result)
                    ->setMessage(
                        trans('plugins/ecommerce::products.form.variation_existed') . ' #' . $result['variation']->id
                    );
            }

            if ($variation->is_default) {
                $request->merge([
                    'variation_default_id' => $variation->id,
                ]);
            }

            $this->postSaveAllVersions(
                [$variation->id => $request->input()],
                $variation->configurable_product_id,
                $response
            );

            ProductVariation::query()->whereNull('product_id')->delete();

            return $response->setMessage(trans('plugins/ecommerce::products.form.updated_variation_success'));
        }

        return $response
            ->setError()
            ->setMessage(trans('plugins/ecommerce::products.form.no_attributes_selected'));
    }

    public function postGenerateAllVersions(
        CreateProductVariationsService $service,
        int|string $id,
        BaseHttpResponse $response
    ) {
        $product = Product::query()->findOrFail($id);

        $variations = $service->execute($product);

        $variationInfo = [];

        foreach ($variations as $variation) {
            /**
             * @var Collection $variation
             */
            $data = $variation->toArray();
            if ((int)$variation->is_default === 1) {
                $data['variation_default_id'] = $variation->id;
            }

            $variationInfo[$variation->id] = $data;
        }

        $this->postSaveAllVersions($variationInfo, $id, $response, false);

        return $response->setMessage(trans('plugins/ecommerce::products.form.created_all_variation_success'));
    }

    public function postStoreRelatedAttributes(
        Request $request,
        StoreAttributesOfProductService $service,
        int|string $id,
        BaseHttpResponse $response
    ) {
        $product = Product::query()->findOrFail($id);

        $attributeSets = $request->input('attribute_sets', []);

        $service->execute($product, $attributeSets);

        return $response->setMessage(trans('plugins/ecommerce::products.form.updated_product_attributes_success'));
    }

    public function getListProductForSearch(Request $request, BaseHttpResponse $response)
    {
        $availableProducts = Product::query()
            ->wherePublished()
            ->where('is_variation', 0)
            ->where('id', '!=', $request->input('product_id', 0))
            ->where('name', 'LIKE', '%' . $request->input('keyword') . '%')
            ->select([
                'id',
                'name',
                'images',
                'image',
                'price',
            ])
            ->simplePaginate(5);

        $includeVariation = $request->input('include_variation', 0);

        return $response->setData(
            view(
                'plugins/ecommerce::products.partials.panel-search-data',
                compact(
                    'availableProducts',
                    'includeVariation'
                )
            )->render()
        );
    }

    public function getRelationBoxes(int|string|null $id, BaseHttpResponse $response)
    {
        $product = null;
        if ($id) {
            $product = Product::query()->find($id);
        }

        $dataUrl = route('products.get-list-product-for-search', ['product_id' => $product ? $product->id : 0]);

        return $response->setData(
            view(
                'plugins/ecommerce::products.partials.extras',
                compact('product', 'dataUrl')
            )->render()
        );
    }

    public function getListProductForSelect(Request $request, BaseHttpResponse $response)
    {
        $availableProducts = Product::query()
            ->wherePublished()
            ->where('is_variation', '<>', 1)
            ->where('name', 'LIKE', '%' . $request->input('keyword') . '%')
            ->select([
                'ec_products.*',
            ])
            ->distinct('ec_products.id');

        $includeVariation = $request->input('include_variation', 0);
        if ($includeVariation) {
            /**
             * @var Builder $availableProducts
             */
            $availableProducts = $availableProducts
                ->join('ec_product_variations', 'ec_product_variations.configurable_product_id', '=', 'ec_products.id')
                ->join(
                    'ec_product_variation_items',
                    'ec_product_variation_items.variation_id',
                    '=',
                    'ec_product_variations.id'
                );
        }

        $availableProducts = $availableProducts->simplePaginate(5);

        foreach ($availableProducts as &$availableProduct) {
            $image = Arr::first($availableProduct->images) ?? null;
            $availableProduct->image_url = RvMedia::getImageUrl($image, 'thumb', false, RvMedia::getDefaultImage());
            $availableProduct->price = $availableProduct->front_sale_price;
            if ($includeVariation) {
                foreach ($availableProduct->variations as &$variation) {
                    $variation->price = $variation->product->front_sale_price;
                    foreach ($variation->variationItems as &$variationItem) {
                        $variationItem->attribute_title = $variationItem->attribute->title;
                    }
                }
            }
        }

        return $response->setData($availableProducts);
    }

    public function postCreateProductWhenCreatingOrder(
        CreateProductWhenCreatingOrderRequest $request,
        BaseHttpResponse $response
    ) {
        $product = Product::query()->create($request->input());

        event(new CreatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, $request, $product));

        return $response
            ->setData(new AvailableProductResource($product))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function getAllProductAndVariations(
        SearchProductAndVariationsRequest $request,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $selectedProducts = collect();
        if ($productIds = $request->input('product_ids', [])) {
            $selectedProducts = Product::query()
                ->wherePublished()
                ->whereIn('id', $productIds)
                ->with(['variationInfo.configurableProduct'])
                ->get();
        }

        $availableProducts = Product::query()
            ->select(['ec_products.*'])
            ->where('is_variation', false)
            ->wherePublished()
            ->with(['variationInfo.configurableProduct']);

        if ($keyword = $request->input('keyword')) {
            $availableProducts = $availableProducts
                ->where(function ($query) use ($keyword) {
                    $query
                        ->where('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sku', 'LIKE', '%' . $keyword . '%');
                });
        }

        if (is_plugin_active('marketplace') && $selectedProducts->count()) {
            $selectedProducts = $selectedProducts->map(function ($item) {
                if ($item->is_variation) {
                    $item->store_id = $item->original_product->store_id;
                }

                if (! $item->store_id) {
                    $item->store_id = 0;
                }

                return $item;
            });
            $storeIds = array_unique($selectedProducts->pluck('store_id')->all());
            $availableProducts = $availableProducts->whereIn('store_id', $storeIds)->with(['store']);
        }

        $availableProducts = $availableProducts->simplePaginate(5);

        return $response->setData(AvailableProductResource::collection($availableProducts)->response()->getData());
    }

    public function postUpdateOrderBy(
        ProductUpdateOrderByRequest $request,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $product = Product::query()->findOrFail($request->input('pk'));
        $product->order = $request->input('value', 0);
        $product->save();

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getProductAttributeSets(BaseHttpResponse $response, int|string|null $id = null): BaseHttpResponse
    {
        $with = [
            'attributes' => function ($query) {
                $query->select(['id', 'slug', 'title', 'attribute_set_id']);
            },
        ];

        $productAttributeSets = ProductAttributeSet::getAllWithSelected($id, $with);

        return $response
            ->setData($productAttributeSets->transform(fn ($item) => $item->only(['attributes', 'id', 'title'])));
    }
}
