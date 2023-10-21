<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Models\Review;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

if (! function_exists('get_product_by_id')) {
    function get_product_by_id(int|string $productId): ?Product
    {
        return Product::query()->find($productId);
    }
}

if (! function_exists('get_products')) {
    function get_products(array $params = []): Collection|LengthAwarePaginator|Product|null
    {
        $params = array_merge([
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
            ],
            'with' => ['slugable'],
            'withCount' => [],
            'withAvg' => [],
        ], $params);

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_products_on_sale')) {
    function get_products_on_sale(array $params = []): Collection|LengthAwarePaginator
    {
        $params = array_merge([
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
            ],
            'with' => [],
            'withCount' => [],
        ], $params);

        return app(ProductInterface::class)->getOnSaleProducts($params);
    }
}

if (! function_exists('get_featured_products')) {
    function get_featured_products(array $params = []): Collection|LengthAwarePaginator
    {
        $params = array_merge([
            'condition' => [
                'ec_products.is_featured' => 1,
                'ec_products.is_variation' => 0,
            ],
            'take' => null,
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'select' => ['ec_products.*'],
            'with' => [],
        ], $params);

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_top_rated_products')) {
    function get_top_rated_products(int $limit = 10, array $with = [], array $withCount = []): Collection|LengthAwarePaginator
    {
        $topProductIds = get_top_rated_product_ids($limit);

        return get_products(array_merge([
                'condition' => [
                    ['ec_products.id', 'IN', $topProductIds],
                    'ec_products.is_variation' => 0,
                ],
                'order_by' => [
                    'reviews_avg' => 'DESC',
                    'ec_products.order' => 'ASC',
                    'ec_products.created_at' => 'DESC',
                ],
                'take' => null,
                'paginate' => [
                    'per_page' => null,
                    'current_paged' => 1,
                ],
                'select' => [
                    'ec_products.*',
                ],
                'with' => $with,
                'withCount' => $withCount,
            ], EcommerceHelper::withReviewsParams()));
    }
}

if (! function_exists('get_top_rated_product_ids')) {
    function get_top_rated_product_ids(int $limit = 10): array
    {
        return Review::query()
            ->wherePublished()
            ->selectRaw('product_id, avg(star) AS star')
            ->groupBy('product_id')
            ->orderBy('star', 'DESC')
            ->limit($limit)
            ->pluck('product_id')
            ->all();
    }
}

if (! function_exists('get_trending_products')) {
    function get_trending_products(array $params = []): Collection|LengthAwarePaginator
    {
        $params = array_merge([
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'take' => 10,
            'order_by' => [
                'ec_products.views' => 'DESC',
            ],
            'select' => ['ec_products.*'],
            'with' => [],
        ], $params);

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_featured_product_categories')) {
    function get_featured_product_categories(): Collection|LengthAwarePaginator
    {
        return ProductCategory::query()
            ->where('is_featured', true)
            ->wherePublished()
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->with('slugable')
            ->get();
    }
}

if (! function_exists('get_product_collections')) {
    function get_product_collections(
        array $condition = [],
        array $with = [],
        array $select = ['*']
    ): Collection {
        return ProductCollection::query()
            ->where($condition)
            ->wherePublished()
            ->select($select)
            ->with($with)
            ->get();
    }
}

if (! function_exists('get_products_by_collections')) {
    function get_products_by_collections(array $params = []): Collection
    {
        return app(ProductInterface::class)->getProductsByCollections($params);
    }
}

if (! function_exists('get_default_product_variation')) {
    function get_default_product_variation(int|string $configurableId): Product|null
    {
        return app(ProductInterface::class)
            ->getProductVariations($configurableId, [
                'condition' => [
                    'ec_products.status' => BaseStatusEnum::PUBLISHED,
                    'ec_products.is_variation' => 1,
                ],
                'take' => 1,
                'order_by' => [
                    'ec_product_variations.is_default' => 'DESC',
                ],
            ]);
    }
}

if (! function_exists('get_product_by_brand')) {
    function get_product_by_brand(array $params): Collection|LengthAwarePaginator
    {
        return app(ProductInterface::class)->getProductByBrands($params);
    }
}

if (! function_exists('the_product_price')) {
    function the_product_price(Product $product, array $htmlWrap = []): string
    {
        $htmlWrapParams = array_merge([
            'open_wrap_price' => '<del>',
            'close_wrap_price' => '</del>',
            'open_wrap_sale' => '<ins>',
            'close_wrap_sale' => '</ins>',
        ], $htmlWrap);

        if ($product->front_sale_price !== $product->price) {
            return $htmlWrapParams['open_wrap_price'] . format_price($product->price) . $htmlWrapParams['close_wrap_price'] .
                $htmlWrapParams['open_wrap_sale'] . format_price($product->front_sale_price) . $htmlWrapParams['close_wrap_sale'];
        }

        return $htmlWrapParams['open_wrap_sale'] . $product->price . $htmlWrapParams['close_wrap_sale'];
    }
}

if (! function_exists('get_related_products')) {
    function get_related_products(Product $product, int $limit = 4): Collection|LengthAwarePaginator
    {
        $params = [
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => $limit,
            'select' => [
                'ec_products.*',
            ],
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
        ];

        $params = array_merge($params, EcommerceHelper::withReviewsParams());

        $relatedIds = $product->products()->allRelatedIds()->toArray();

        if (! empty($relatedIds)) {
            $params['condition'][] = ['ec_products.id', 'IN', $relatedIds];
        } else {
            $params['condition'][] = ['ec_products.id', '!=', $product->getKey()];
        }

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_cross_sale_products')) {
    function get_cross_sale_products(Product $product, int $limit = 4, array $with = []): EloquentCollection
    {
        $with = array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $with);

        $reviewParams = EcommerceHelper::withReviewsParams();

        return $product
            ->crossSales()
            ->limit($limit)
            ->with($with)
            ->wherePublished()
            ->notOutOfStock()
            ->withCount($reviewParams['withCount'])
            ->withAvg($reviewParams['withAvg'][0], $reviewParams['withAvg'][1])
            ->get();
    }
}

if (! function_exists('get_up_sale_products')) {
    function get_up_sale_products(Product $product, int $limit = 4, array $with = []): EloquentCollection
    {
        $with = array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $with);

        return $product
            ->upSales()
            ->limit($limit)
            ->with($with)
            ->wherePublished()
            ->notOutOfStock()
            ->withCount(EcommerceHelper::withReviewsParams()['withCount'])
            ->get();
    }
}

if (! function_exists('get_cart_cross_sale_products')) {
    function get_cart_cross_sale_products(array $productIds, int $limit = 4, array $with = []): EloquentCollection
    {
        $crossSaleIds = DB::table('ec_product_cross_sale_relations')
            ->whereIn('from_product_id', $productIds)
            ->pluck('to_product_id')
            ->all();

        $params = [
            'condition' => [
                ['ec_products.id', 'IN', $crossSaleIds],
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => $limit,
            'select' => [
                'ec_products.*',
            ],
            'with' => array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $with),
        ];

        $params = array_merge($params, EcommerceHelper::withReviewsParams());

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_product_attributes_with_set')) {
    function get_product_attributes_with_set(Product $product, int|string $setId): array
    {
        $productAttributes = app(ProductInterface::class)->getRelatedProductAttributes($product);

        $attributes = [];

        foreach ($productAttributes as $attribute) {
            if ($attribute->attribute_set_id === $setId) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}

if (! function_exists('handle_next_attributes_in_product')) {
    function handle_next_attributes_in_product(
        Collection $productAttributes,
        Collection $productVariationsInfo,
        int|string|null $setId,
        array $selectedAttributes,
        string|null $key,
        array $variationNextIds,
        Collection|null $variationInfo = null,
        array $unavailableAttributeIds = []
    ): array {
        foreach ($productAttributes as $attribute) {
            if ($variationInfo != null && ! $variationInfo->where('id', $attribute->id)->count()) {
                $unavailableAttributeIds[] = $attribute->id;
            }
            if (in_array($attribute->id, $selectedAttributes)) {
                $variationIds = $productVariationsInfo
                    ->where('attribute_set_id', $setId)
                    ->where('id', $attribute->id)
                    ->pluck('variation_id')
                    ->toArray();

                if ($key == 0) {
                    $variationNextIds = $variationIds;
                } else {
                    $variationNextIds = array_intersect($variationNextIds, $variationIds);
                }
            }
        }

        return [$variationNextIds, $unavailableAttributeIds];
    }
}
